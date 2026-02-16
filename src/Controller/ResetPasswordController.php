<?php

namespace App\Controller;

use App\Dto\Auth\RegisterMailDto;
use App\Dto\Auth\VerifyOtpDto;
use App\Entity\Auth\UserStatus;
use App\Form\Auth\ChangePasswordFormType;
use App\Form\Auth\OtpVerifyFormType;
use App\Form\Auth\RegistrationMailFormType;
use App\Repository\Auth\UserRepository;
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
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AuthLayoutController
{
    private const CACHE_TTL = 300; // 5 minutes
    private const CACHE_PREFIX = 'reset_password_';

    public function __construct(
        public ThemeHelper $theme,
        protected TranslatorInterface $translator,
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private CacheInterface $cache,
    ) {
        parent::__construct($theme, $translator);
    }

    #[Route('/', name: 'app_forgot_password_request')]
    public function forgetPassword(
        Request $request,
        MailerInterface $mailer,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('homepage');
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
                $cacheKey = self::CACHE_PREFIX . 'code_' . $user->getId();

                try {
                    // Check if token already exists
                    $existingData = $this->cache->get($cacheKey, function (ItemInterface $item) {
                        return null;
                    });

                    if ($existingData !== null) {
                        $this->addFlash(
                            'error',
                            $this->translator->trans(
                                'A reset code has already been sent to your email. Please check your inbox or try again later.'
                            )
                        );
                        return $this->redirectToRoute('app_forgot_password_request');
                    } else {
                        $code = random_int(100000, 999999);

                        // Store code in cache
                        $this->cache->delete($cacheKey);
                        $this->cache->get($cacheKey, function (ItemInterface $item) use ($user, $code) {
                            $item->expiresAfter(self::CACHE_TTL);
                            return [
                                'user_id' => $user->getId(),
                                'code' => $code,
                                'email' => $user->getEmail(),
                                'verified' => false,
                            ];
                        });

                        $email = (new TemplatedEmail())
                            ->to($user->getEmail())
                            ->subject($this->translator->trans('Reset Password'))
                            ->htmlTemplate('reset_password/verify-email.html.twig')
                            ->context([
                                'code' => $code
                            ])
                            ->locale($this->translator->getLocale());

                        $mailer->send($email);

                        $request->getSession()->set('forget_password_email', $dto->email);
                    }
                } catch (\Exception $e) {
                    $this->addFlash(
                        'error',
                        $this->translator->trans('Sorry, an unknown error occured.')
                    );
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
            return $this->redirectToRoute('homepage');
        }
        $email = $request->getSession()->get('forget_password_email');
        $dto = new VerifyOtpDto();
        $dto->email = $email;
        $form = $this->createForm(OtpVerifyFormType::class, $dto);
        $form->handleRequest($request);

        $error = false;
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->userRepository->findOneBy(['email' => $email]);
            if (!$user) {
                $this->addFlash(
                    'error',
                    $this->translator->trans("Sorry, an unknown error occured.")
                );
                $error = true;
            }

            /** @var VerifyOtpDto */
            $dto = $form->getData();

            if (!$error) {
                $cacheKey = self::CACHE_PREFIX . 'code_' . $user->getId();

                try {
                    $tokenData = $this->cache->get($cacheKey, function (ItemInterface $item) {
                        return null;
                    });

                    if ($tokenData === null) {
                        $this->addFlash(
                            'error',
                            $this->translator->trans('Code expired.')
                        );
                        $error = true;
                    } else {
                        if ($tokenData['code'] != $dto->code) {
                            $this->addFlash(
                                'error',
                                $this->translator->trans("Invalid token."),
                            );
                            $error = true;
                        }

                        if (!$error) {
                            // Generate reset token
                            $resetToken = bin2hex(random_bytes(32));

                            // Update cache with reset token - delete and recreate
                            $this->cache->delete($cacheKey);
                            $this->cache->get($cacheKey, function (ItemInterface $item) use ($tokenData, $resetToken) {
                                $item->expiresAfter(self::CACHE_TTL);
                                $tokenData['verified'] = true;
                                $tokenData['reset_token'] = $resetToken;
                                return $tokenData;
                            });

                            $this->addFlash(
                                'success',
                                $this->translator->trans('Code validated.')
                            );
                            $request->getSession()->set('reset_token', $resetToken);
                            $request->getSession()->set('reset_user_id', $user->getId());
                            return $this->redirectToRoute('app_forget_password_reset');
                        }
                    }
                } catch (\Exception $e) {
                    $this->addFlash('error', $this->translator->trans('An error occurred. Please try again.'));
                    $error = true;
                }
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
        $userId = $request->getSession()->get('reset_user_id');

        $error = false;
        $tokenData = null;

        if (!$resetToken || !$userId) {
            $this->addFlash(
                'error',
                $this->translator->trans("Invalid token."),
            );
            $error = true;
        }

        if (!$error) {
            $cacheKey = self::CACHE_PREFIX . 'code_' . $userId;

            try {
                $tokenData = $this->cache->get($cacheKey, function (ItemInterface $item) {
                    return null;
                });

                if ($tokenData === null) {
                    $this->addFlash(
                        'error',
                        $this->translator->trans('Code expired.')
                    );
                    $error = true;
                } else {
                    if (
                        !isset($tokenData['reset_token']) ||
                        $tokenData['reset_token'] !== $resetToken ||
                        !$tokenData['verified']
                    ) {
                        $this->addFlash(
                            'error',
                            $this->translator->trans("Invalid token."),
                        );
                        $error = true;
                    }
                }
            } catch (\Exception $e) {
                $this->addFlash('error', $this->translator->trans('Sorry, an unknown error occured.'));
                $error = true;
            }
        }

        if ($error) {
            return $this->redirectToRoute('app_forgot_password_request');
        }

        $form = $this->createForm(ChangePasswordFormType::class, null, [
            'attr' => [
                'novalidate' => true
            ]
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->userRepository->find($userId);

            if (!$user) {
                $this->addFlash('error', $this->translator->trans("User not found."));
                return $this->redirectToRoute('app_forgot_password_request');
            }

            $encodedPassword = $userPasswordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            $user->setPassword($encodedPassword);
            $user->setStatus(UserStatus::Active);
            $this->entityManager->flush();

            // Delete cache entry
            $cacheKey = self::CACHE_PREFIX . 'code_' . $userId;
            $this->cache->delete($cacheKey);

            // Clear session
            $request->getSession()->remove('reset_token');
            $request->getSession()->remove('reset_user_id');
            $request->getSession()->remove('forget_password_email');

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
