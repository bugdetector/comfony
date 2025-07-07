<?php

namespace App\EventSubscriber;

use App\Entity\File\File;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Mapping\PostRemove;
use Doctrine\ORM\Mapping\PreRemove;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsEntityListener(entity: File::class)]
class FileListener
{
    public function __construct(
        private KernelInterface $kernel,
    ) {
    }

    #[PreRemove]
    public function preRemove(File $file)
    {
        // Ensure object is loaded before post remove.
        $file->getFilePath();
    }

    #[PostRemove]
    public function postRemove(File $file)
    {
        $projectDir = $this->kernel->getProjectDir();
        $filePath = $projectDir . '/public/uploads' . $file->getFilePath();
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
