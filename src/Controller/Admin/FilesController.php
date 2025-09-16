<?php

namespace App\Controller\Admin;

use App\Entity\File\File;
use App\Entity\File\FileStatus;
use App\Form\File\FileType;
use App\Repository\FileRepository;
use App\Service\FileManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/files')]
class FilesController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator
    ) {
    }
    #[Route('/', name: 'app_admin_files_index', methods: ['GET'])]
    public function index(FileRepository $fileRepository): Response
    {
        return $this->render('admin/files/index.html.twig', [
            'files' => $fileRepository->findAll(),
            'title' => $this->translator->trans('Files'),
        ]);
    }

    #[Route('/new', name: 'app_admin_files_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        FileManager $fileManager,
    ): Response {
        $file = new File();
        $form = $this->createForm(FileType::class, $file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('file')->getData();
            if ($uploadedFile) {
                try {
                    $fileManager->saveUploadedFile(
                        $file,
                        $uploadedFile,
                        FileStatus::Permanent
                    );
                    $this->addFlash('success', $this->translator->trans('File uploaded successfully.'));
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
            'title' => $this->translator->trans('Upload New File')
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_files_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        File $file,
        FileManager $fileManager,
    ): Response {
        $form = $this->createForm(FileType::class, $file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile */
            $uploadedFile = $form->get('file')->getData();
            if ($uploadedFile) {
                try {
                    $fileManager->saveUploadedFile(
                        $file,
                        $uploadedFile,
                        FileStatus::Permanent
                    );
                    $this->addFlash('success', $this->translator->trans('File uploaded successfully.'));
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
            'title' => $this->translator->trans('Edit File: { name }', [
                "name" => $file->getFileName()
            ])
        ]);
    }

    #[Route('/{id}', name: 'app_admin_files_delete', methods: ['POST'])]
    public function delete(Request $request, File $file, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $file->getId(), $request->request->get('_token'))) {
            $entityManager->remove($file);
            $entityManager->flush();
            $this->addFlash('success', $this->translator->trans('File deleted successfully.'));
        }

        return $this->redirectToRoute('app_admin_files_index', [], Response::HTTP_SEE_OTHER);
    }
}
