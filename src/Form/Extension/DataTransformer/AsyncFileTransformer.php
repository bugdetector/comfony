<?php

namespace App\Form\Extension\DataTransformer;

use App\Entity\File\File;
use App\Repository\FileRepository;
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
    public function transform(mixed $file): ?File
    {
        return $file;
    }

    /**
     * @throws TransformationFailedException if the given value is not an array
     *                                       or if no matching choice could be
     *                                       found for some given value
     */
    public function reverseTransform(mixed $fileId): ?File
    {
        return $fileId ? $this->fileRepository->find($fileId) : null;
    }
}
