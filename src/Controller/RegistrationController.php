<?php

namespace App\Controller;

use App\Entity\Auth\User;
use App\Form\RegistrationFormType;
use App\Repository\Auth\UserRepository;
use App\Security\LoginFormAuthenticator;
use App\Theme\BaseTheme\AuthLayoutController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AuthLayoutController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        LoginFormAuthenticator $authenticator,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            /** @var UserRepository */
            $userRepository = $entityManager->getRepository(User::class);
            if ($userRepository->count([]) == 0) {
                $user->setRoles([User::ROLE_SUPER_ADMIN]);
            }

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            )->setStatusCode(400);
        }

        if ($this->getUser()) {
            return $this->redirectToRoute('homepage');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ], new Response(
            null,
            $form->isSubmitted() && !$form->isValid() ? 422 : 200
        ));
    }
}
