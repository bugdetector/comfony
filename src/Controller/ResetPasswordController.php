<?php

namespace App\Controller;

use App\Dto\Auth\RegisterMailDto;
use App\Dto\Auth\VerifyOtpDto;
use App\Entity\Auth\ForgetPasswordToken;
use App\Entity\Auth\UserStatus;
use App\Form\Auth\ChangePasswordFormType;
use App\Form\Auth\OtpVerifyFormType;
use App\Form\Auth\RegistrationMailFormType;
use App\Repository\Auth\UserRepository;
use App\Repository\Auth\ForgetPasswordTokenRepository;
use App\Security\LoginFormAuthenticator;
use App\Theme\BaseTheme\AuthLayoutController;
use App\Theme\ThemeHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AuthLayoutController
{
    public function __construct(
        public ThemeHelper $theme,
        protected TranslatorInterface $translator,
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private ForgetPasswordTokenRepository $forgetPasswordTokenRepository,
    ) {
        parent::__construct($theme, $translator);
    }

    #[Route('/', name: 'app_forgot_password_request')]
    public function forgetPassword(
        Request $request,
        MailerInterface $mailer,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_tenant_customer_dashboard');
        }

        $form = $this->createForm(RegistrationMailFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var RegisterMailDto */
            $dto = $form->getData();
            $user = $this->userRepository->createQueryBuilder('u')
                ->where('u.email = :email')
                ->andWhere('u.status IN (:statuses)')
                ->setParameter('email', $dto->email)
                ->setParameter('statuses', [UserStatus::Active, UserStatus::Blocked])
                ->getQuery()
                ->getOneOrNullResult();


            if ($user) {
                /** @var ForgetPasswordToken */
                $token = $this->forgetPasswordTokenRepository->findOneBy([
                    "user" => $user
                ]) ?: new ForgetPasswordToken();
                if ($token->getId() && $token->isValid()) {
                    $this->addFlash('error', "Too many attempts to send mail.");
                } else {
                    $token->setUser($user);
                    $token->setCode(random_int(100000, 999999));
                    $token->setResetToken(null);
                    $this->entityManager->persist($token);
                    $this->entityManager->flush();

                    $email = (new TemplatedEmail())
                        ->to($user->getEmail())
                        ->subject($this->translator->trans('Reset Password'))
                        ->htmlTemplate('reset_password/verify-email.html.twig')
                        ->context([
                            'code' => $token->getCode()
                        ]);

                    $mailer->send($email);

                    $request->getSession()->set('forget_password_email', $dto->email);
                }
            }
            return $this->redirectToRoute('app_forget_password_verify');
        }

        return $this->render('reset_password/request.html.twig', [
            'form' => $form->createView(),
            'title' => $this->translator->trans('Reset Password'),
        ], new Response(
            null,
            $form->isSubmitted() && !$form->isValid() ? 422 : 200
        ));
    }

    #[Route('/verify', name: 'app_forget_password_verify')]
    public function verifyEmail(
        Request $request,
    ) {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_tenant_customer_dashboard');
        }
        $email = $request->getSession()->get('forget_password_email');
        $dto = new VerifyOtpDto();
        $dto->email = $email;
        $form = $this->createForm(OtpVerifyFormType::class, $dto);
        $form->handleRequest($request);

        $error = false;
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->userRepository->findOneBy(['email' => $email]);
            /** @var VerifyOtpDto */
            $dto = $form->getData();
            /** @var ForgetPasswordToken */
            $token = $this->forgetPasswordTokenRepository->findOneBy([
                "user" => $user,
                "code" => $dto->code,
                "reset_token" => null
            ]);

            if (!$token) {
                $this->addFlash(
                    'error',
                    $this->translator->trans("Invalid token."),
                );
                $error = true;
            }
            if (!$error && !$token->isValid()) {
                $this->addFlash(
                    'error',
                    $this->translator->trans('Code expired.')
                );
                $error = true;
            }
            if (!$error) {
                $token->setResetToken(
                    uniqid("reset_token_")
                );
                $this->entityManager->persist($token);
                $this->entityManager->flush();
                $this->addFlash(
                    'success',
                    $this->translator->trans('Code validated.')
                );
                $request->getSession()->set('reset_token', $token->getResetToken());
                return $this->redirectToRoute('app_forget_password_reset');
            }
        }

        return $this->render('reset_password/verify.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
            'title' => $this->translator->trans('Verify Email'),
        ], new Response(
            null,
            $form->isSubmitted() && !$form->isValid() ? 422 : 200
        ));
    }

    #[Route('/reset', name: 'app_forget_password_reset')]
    public function resetPassword(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
    ): Response {

        $resetToken = $request->getSession()->get('reset_token');
        /** @var ForgetPasswordToken */
        $token = $this->forgetPasswordTokenRepository->findOneBy([
            'reset_token' => $resetToken,
        ]);
        $error = false;
        if (!$token) {
            $this->addFlash(
                'error',
                $this->translator->trans("Invalid token."),
            );
            $error = true;
        }
        if (!$error && !$token->isValid()) {
            $this->addFlash(
                'error',
                $this->translator->trans('Code expired.')
            );
            $error = true;
        }
        if ($error) {
            return $this->redirectToRoute('app_forget_password');
        }

        $form = $this->createForm(ChangePasswordFormType::class, null, [
            'attr' => [
                'novalidate' => true
            ]
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->remove($token);
            $user = $token->getUser();
            $encodedPassword = $userPasswordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            $user->setPassword($encodedPassword);
            $user->setStatus(UserStatus::Active);
            $this->entityManager->flush();

            $security->login($user, "security.authenticator.remember_me.main");

            $this->addFlash(
                'success',
                $this->translator->trans("Password reset successfully.")
            );
            return $this->redirectToRoute('homepage');
        }

        return $this->render('reset_password/reset.html.twig', [
            'form' => $form->createView(),
            'title' => $this->translator->trans('Reset Password'),
        ], new Response(
            null,
            $form->isSubmitted() && !$form->isValid() ? 422 : 200
        ));
    }
}
