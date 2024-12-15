<?php

namespace App\Form\Extension\DataTransformer;

use App\Entity\File\File;
use App\Repository\FileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class AsyncFileTransformer implements DataTransformerInterface
{
    public function __construct(
        private FileRepository $fileRepository
    ) {
    }

    /**
     * @throws TransformationFailedException if the given value is not an array
     */
    public function transform(mixed $files): File|Collection|null
    {
        if($files instanceof PersistentCollection && !$files->isInitialized()) {
            $files->initialize();
        }
        return $files;
    }

    /**
     * @throws TransformationFailedException if the given value is not an array
     *                                       or if no matching choice could be
     *                                       found for some given value
     */
    public function reverseTransform(mixed $fileIds): File|Collection|null
    {
        if (is_array($fileIds)) {
            return new ArrayCollection(
                $fileIds ? $this->fileRepository->findBy([
                    "id" => $fileIds
                ]) : []
            );
        } else {
            return $fileIds ? $this->fileRepository->find($fileIds) : null;
        }
    }
}
