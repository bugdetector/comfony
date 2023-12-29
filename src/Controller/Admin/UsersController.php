<?php

namespace App\Controller\Admin;

use App\Entity\Auth\User;
use App\Form\Auth\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/users')]
class UsersController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator
    ) {
    }

    #[Route('/', name: 'app_admin_users_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->render('admin/users/index.html.twig', [
            'title' => $this->translator->trans('Users'),
            'list_params' => [
                'query' => (string) $request->query->get('q', ''),
                'page' => (int) $request->query->get('page', 1)
            ]
        ]);
    }

    #[Route('/new', name: 'app_admin_users_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'user.created_successfully');
            return $this->redirectToRoute('app_admin_users_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/users/new.html.twig', [
            'user' => $user,
            'form' => $form,
            'title' => $this->translator->trans('Add new user')
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_users_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'user.updated_successfully');
            return $this->redirectToRoute('app_admin_users_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'form' => $form,
            'title' => $this->translator->trans('Edit User :name', [
                ":name" => $user->getName()
            ])
        ]);
    }

    #[Route('/{id}', name: 'app_admin_users_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'user.deleted_successfully');
        }

        return $this->redirectToRoute('app_admin_users_index', [], Response::HTTP_SEE_OTHER);
    }
}
