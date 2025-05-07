<?php

namespace App\Service;

use App\Entity\File\File;
use App\Entity\File\FileStatus;
use App\Message\FileUploaded;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileManager
{
    public function __construct(
        private KernelInterface $kernel,
        private SluggerInterface $slugger,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus,
        private LoggerInterface $logger,
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

        $event = new FileUploaded($file->getId());
        $this->bus->dispatch($event);
    }

    public function compressImage(File $file)
    {
        $projectDir = $this->kernel->getProjectDir();
        $fullPath = $projectDir . '/public/uploads' . $file->getFilePath();
        $image = imagecreatefromstring(file_get_contents($fullPath));
        if (!$image) {
            $this->logger->error(
                "Image create failed.",
                [
                    'id' => $file->getId(),
                    'path' => $file->getFilePath(),
                ]
            );
            return;
        }
        if ($file->getMimeType() == 'image/png') {
            imagesavealpha($image, true);
            imagepng($image, $fullPath, 9);
            $file->setFileSize(filesize($fullPath));
        } elseif (in_array($file->getMimeType(), ['image/jpg', 'image/jpeg'])) {
            imagejpeg($image, $fullPath, 75);
            $file->setFileSize(filesize($fullPath));
        }

        imagedestroy($image);

        $file->setCompressed(true);
        $this->entityManager->persist($file);
        $this->entityManager->flush();
    }
}
