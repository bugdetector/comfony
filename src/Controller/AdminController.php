<?php

namespace App\Controller;

use App\Repository\Auth\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator
    ) {
    }

    #[Route('/admin', name: 'app_admin')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/index.html.twig', [
            'title' => $this->translator->trans("Dashboard"),
            'user_count' => $userRepository->count([])
        ]);
    }
}
