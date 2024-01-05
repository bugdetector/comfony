<?php

namespace App\Twig\Components;

use App\Entity\File\File;
use App\Repository\FileRepository;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'file_preview')]
final class FilePreviewComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?File $file = null;

    #[LiveProp(writable: true)]
    public ?int $fileId = null;

    public function __construct(
        private FileRepository $fileRepository
    ) {
    }

    public function getFile(): ?File
    {
        return $this->file ?: (
            $this->fileId ? $this->fileRepository->find($this->fileId) : null
        );
    }
}
