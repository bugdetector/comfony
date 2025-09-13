<?php

namespace App\Controller\Admin;

use App\Entity\Configuration\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/configuration')]
class ConfigurationController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator
    ) {
    }

    #[Route('/', name: 'app_admin_configuration_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/configuration/index.html.twig', [
            'title' => $this->translator->trans('Configurations'),
        ]);
    }

    #[Route('/new', name: 'app_admin_configuration_new', methods: ['GET'])]
    public function new(): Response
    {
        $configuration = new Configuration();
        return $this->render('admin/configuration/edit.html.twig', [
            'title' => $this->translator->trans('Add New Configuration'),
            'configuration' => $configuration,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_configuration_edit', methods: ['GET'])]
    public function edit(Configuration $configuration): Response
    {
        return $this->render('admin/configuration/edit.html.twig', [
            'title' => $this->translator->trans(
                'Edit Configuration {congigKey}',
                ['congigKey' => $configuration->getConfigKey()]
            ),
            'configuration' => $configuration,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_configuration_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Configuration $configuration,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $configuration->getId(), $request->request->get('_token'))) {
            $entityManager->remove($configuration);
            $entityManager->flush();
            $this->addFlash('success', $this->translator->trans('Configuration deleted successfully.'));
        }

        return $this->redirectToRoute('app_admin_configuration_index', [], Response::HTTP_SEE_OTHER);
    }
}
