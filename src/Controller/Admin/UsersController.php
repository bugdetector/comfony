<?php

namespace App\Controller\Admin;

use App\Entity\Auth\User;
use App\Form\Auth\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
    public function index(): Response
    {
        return $this->render('admin/users/index.html.twig', [
            'title' => $this->translator->trans('Users'),
        ]);
    }

    #[Route('/new', name: 'app_admin_users_new', methods: ['GET'])]
    public function new(): Response
    {
        $user = new User();
        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'title' => $this->translator->trans('Add New User')
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_users_edit', methods: ['GET'])]
    public function edit(User $user): Response
    {
        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'title' => $this->translator->trans('Edit User :name', [
                "name" => $user->getName()
            ])
        ]);
    }

    #[Route('/{id}', name: 'app_admin_users_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', $translator->trans('user.deleted_successfully'));
        }

        return $this->redirectToRoute('app_admin_users_index', [], Response::HTTP_SEE_OTHER);
    }
}
