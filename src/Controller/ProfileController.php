<?php

namespace App\Controller;

use App\Theme\BaseTheme\DefaultLayoutController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends DefaultLayoutController
{
    #[Route('/profile', name: 'app_profile', methods: ['GET', 'POST'],)]
    #[IsGranted('ROLE_USER')]
    public function profile()
    {
        return $this->render('profile/index.html.twig', [
            'title' => $this->translator->trans('Profile')
        ]);
    }
}
