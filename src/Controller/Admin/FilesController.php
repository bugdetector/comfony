<?php

namespace App\Controller\Admin;

use App\Entity\File\File;
use App\Entity\File\FileStatus;
use App\Form\File\FileType;
use App\Repository\FileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/files')]
class FilesController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator
    ) {
    }
    #[Route('/', name: 'app_admin_files_index', methods: ['GET'])]
    public function index(Request $request, FileRepository $fileRepository): Response
    {
        return $this->render('admin/files/index.html.twig', [
            'files' => $fileRepository->findAll(),
            'title' => $this->translator->trans('Files'),
            'list_params' => [
                'query' => (string) $request->query->get('q', ''),
                'page' => (int) $request->query->get('page', 1)
            ]
        ]);
    }

    #[Route('/new', name: 'app_admin_files_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $file = new File();
        $form = $this->createForm(FileType::class, $file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('file')->getData();
            if ($uploadedFile) {
                try {
                    File::saveUploadedFile(
                        $file,
                        $uploadedFile,
                        $slugger,
                        $entityManager,
                        FileStatus::Permanent
                    );
                    $this->addFlash('success', $this->translator->trans('file.uploaded_successfully'));
                    return $this->redirectToRoute('app_admin_files_index', [], Response::HTTP_SEE_OTHER);
                } catch (FileException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
            return $this->redirectToRoute('app_admin_files_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/files/new.html.twig', [
            'file' => $file,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_files_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        File $file,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(FileType::class, $file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile */
            $uploadedFile = $form->get('file')->getData();
            if ($uploadedFile) {
                try {
                    File::saveUploadedFile(
                        $file,
                        $uploadedFile,
                        $slugger,
                        $entityManager,
                        FileStatus::Permanent
                    );
                    $this->addFlash('success', $this->translator->trans('file.uploaded_successfully'));
                    return $this->redirectToRoute('app_admin_files_index', [], Response::HTTP_SEE_OTHER);
                } catch (FileException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            } else {
                return $this->redirectToRoute('app_admin_files_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('admin/files/edit.html.twig', [
            'file' => $file,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_files_delete', methods: ['POST'])]
    public function delete(Request $request, File $file, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $file->getId(), $request->request->get('_token'))) {
            $entityManager->remove($file);
            $entityManager->flush();

            $filePath = $file->getFilePath();
            if ($file->getFilePath() && is_file($filePath)) {
                unlink("uploads" . $filePath);
            }
            $this->addFlash('success', $this->translator->trans('file.deleted_successfully'));
        }

        return $this->redirectToRoute('app_admin_files_index', [], Response::HTTP_SEE_OTHER);
    }
}
