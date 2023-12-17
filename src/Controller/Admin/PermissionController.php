<?php

namespace App\Controller\Admin;

use App\Entity\Auth\Permission;
use App\Form\Auth\PermissionType;
use App\Repository\Auth\PermissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/permission')]
class PermissionController extends AbstractController
{
    #[Route('/', name: 'app_admin_permission_index', methods: ['GET'])]
    public function index(PermissionRepository $permissionRepository): Response
    {
        return $this->render('admin/permission/index.html.twig', [
            'permissions' => $permissionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_permission_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $permission = new Permission();
        $form = $this->createForm(PermissionType::class, $permission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($permission);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_permission_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/permission/new.html.twig', [
            'permission' => $permission,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_permission_show', methods: ['GET'])]
    public function show(Permission $permission): Response
    {
        return $this->render('admin/permission/show.html.twig', [
            'permission' => $permission,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_permission_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Permission $permission, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PermissionType::class, $permission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_permission_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/permission/edit.html.twig', [
            'permission' => $permission,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_permission_delete', methods: ['POST'])]
    public function delete(Request $request, Permission $permission, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $permission->getId(), $request->request->get('_token'))) {
            $entityManager->remove($permission);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_permission_index', [], Response::HTTP_SEE_OTHER);
    }
}
