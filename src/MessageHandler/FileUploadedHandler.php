<?php

namespace App\MessageHandler;

use App\Message\FileUploaded;
use App\Repository\FileRepository;
use App\Service\FileManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class FileUploadedHandler
{
    public function __construct(
        private FileRepository $fileRepository,
        private FileManager $fileManager,
    ) {
    }

    public function __invoke(FileUploaded $message): void
    {
        $file = $this->fileRepository->find($message->fileId);
        if (!$file) {
            return;
        }
        if ($file->isImage() && !$file->isCompressed()) {
            $this->fileManager->compressImage($file);
        }
    }
}
