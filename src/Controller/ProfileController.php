<?php

namespace App\Controller;

use App\Entity\Auth\User;
use App\Form\Auth\ProfileType;
use App\Theme\BaseTheme\DefaultLayoutController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends DefaultLayoutController
{
    #[Route('/profile', name: 'app_profile', methods: ['GET', 'POST'],)]
    #[IsGranted('ROLE_USER')]
    public function profile(
        Request $request,
        Security $security,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ) {
        /** @var User */
        $user = $security->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($password = $form->get('plainPassword')->getData()) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $password
                    )
                );
            }
            $entityManager->flush();
            $this->addFlash('success', $this->translator->trans('Profile updated successfully'));
            return $this->redirectToRoute('app_profile', status: Response::HTTP_SEE_OTHER);
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'form' => $form,
            'title' => $this->translator->trans('Profile')
        ]);
    }
}
