<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/category')]
class CategoryController extends AbstractController
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    #[Route('/', name: 'app_admin_category_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/category/index.html.twig', [
            'title' => $this->translator->trans('Categories'),
        ]);
    }
}
