<?php

namespace App\Service;

use App\Entity\File\File;
use App\Entity\File\FileStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileManager
{
    public function __construct(
        private KernelInterface $kernel,
        private SluggerInterface $slugger,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function saveUploadedFile(
        File $file,
        UploadedFile $uploadedFile,
        FileStatus $fileStatus = FileStatus::Temporary,
        array $nameParts = ['files', 'file']
    ) {
        $projectDir = $this->kernel->getProjectDir();
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $size = $uploadedFile->getSize();
        $mimeType = $uploadedFile->getMimeType();
        $extension = $uploadedFile->guessExtension();
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;
        $directory = "/" . implode('/', $nameParts) . "/";
        $uploadedFile->move(
            $projectDir . '/public/uploads' . $directory,
            $newFilename
        );
        if ($file->getFilePath()) {
            unlink($projectDir . '/public/uploads' . $file->getFilePath());
        }
        $file->setFileName($originalFilename);
        $file->setFilePath($directory . $newFilename);
        $file->setFileSize($size);
        $file->setMimeType($mimeType);
        $file->setExtension($extension);
        $file->setStatus($fileStatus);
        $this->entityManager->persist($file);
        $this->entityManager->flush();
    }
}
