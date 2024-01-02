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
                return $this->saveUploadedFile(
                    $file,
                    $uploadedFile,
                    $slugger,
                    $entityManager
                );
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
                return $this->saveUploadedFile(
                    $file,
                    $uploadedFile,
                    $slugger,
                    $entityManager
                );
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
            $this->addFlash('success', $this->translator->trans('file.deleted_successfully'));
        }

        return $this->redirectToRoute('app_admin_files_index', [], Response::HTTP_SEE_OTHER);
    }

    private function saveUploadedFile(
        File $file,
        UploadedFile $uploadedFile,
        SluggerInterface $slugger,
        EntityManagerInterface $entityManager
    ) {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $size = $uploadedFile->getSize();
        $mimeType = $uploadedFile->getMimeType();
        $extension = $uploadedFile->guessExtension();
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;
        try {
            $directory =  '/files/file/';
            $uploadedFile->move(
                $this->getParameter('uploads_directory') . $directory,
                $newFilename
            );
            if ($file->getFilePath()) {
                unlink($this->getParameter('uploads_directory') . $file->getFilePath());
            }
            $file->setFileName($originalFilename);
            $file->setFilePath($directory . $newFilename);
            $file->setFileSize($size);
            $file->setMimeType($mimeType);
            $file->setExtension($extension);
            $file->setStatus(FileStatus::Permanent);
            $entityManager->persist($file);
            $entityManager->flush();
            $this->addFlash('success', $this->translator->trans('file.saved_successfully'));
            return $this->redirectToRoute('app_admin_files_index', [], Response::HTTP_SEE_OTHER);
        } catch (FileException $e) {
            $this->addFlash('error', $e->getMessage());
        }
    }
}
