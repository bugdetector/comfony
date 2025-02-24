<?php

namespace App\Entity\File;

use App\Repository\FileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: FileRepository::class)]
#[ORM\Table(name: 'files')]
#[Broadcast(
    topics: ['files'],
    private: true
)]
class File
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = 0;

    #[ORM\Column(length: 255)]
    private ?string $file_name = "";

    #[ORM\Column(length: 500)]
    private ?string $file_path = "";

    #[ORM\Column]
    private ?int $file_size = 0;

    #[ORM\Column(length: 255)]
    private ?string $mime_type = "";

    #[ORM\Column(length: 255)]
    private ?string $extension = "";

    #[ORM\Column(enumType: FileStatus::class)]
    private ?FileStatus $status = FileStatus::Temporary;

    #[ORM\Column]
    private ?bool $compressed = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileName(): ?string
    {
        return $this->file_name;
    }

    public function setFileName(string $file_name): static
    {
        $this->file_name = $file_name;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->file_path;
    }

    public function setFilePath(string $file_path): static
    {
        $this->file_path = $file_path;

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->file_size;
    }

    public function setFileSize(int $file_size): static
    {
        $this->file_size = $file_size;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mime_type;
    }

    public function setMimeType(string $mime_type): static
    {
        $this->mime_type = $mime_type;

        return $this;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): static
    {
        $this->extension = $extension;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status->value;
    }

    public function setStatus(FileStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function __toString()
    {
        return 'uploads' . $this->file_path;
    }

    public function isImage(): bool
    {
        return str_contains($this->mime_type, "image/");
    }

    public function isVideo(): bool
    {
        return str_contains($this->mime_type, "video/");
    }

    public function isPdf(): bool
    {
        return $this->mime_type == "application/pdf";
    }

    public function isPreviewable()
    {
        return $this->isImage() ||
            $this->isVideo() ||
            $this->isPdf();
    }

    public function isCompressed(): ?bool
    {
        return $this->compressed;
    }

    public function setCompressed(bool $compressed): static
    {
        $this->compressed = $compressed;

        return $this;
    }
}
