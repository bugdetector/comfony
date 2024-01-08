<?php

namespace App\Controller;

use App\Entity\Page\Page;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    #[Route('/{slug}', name: 'app_page_view', priority: -100)]
    public function index(Page $page): Response
    {
        if (!($page->isPublished())) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }
        return $this->render('page/index.html.twig', [
            'title' => $page->getTitle(),
            'page' => $page,
        ]);
    }
}
