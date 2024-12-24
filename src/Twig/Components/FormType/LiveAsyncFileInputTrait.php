<?php

namespace App\Twig\Components\FormType;

use App\Entity\File\File;
use App\Entity\File\FileStatus;
use App\Repository\FileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;

trait LiveAsyncFileInputTrait
{
    use ComponentWithFormTrait;

    protected FileRepository $fileRepository;
    protected SluggerInterface $slugger;
    protected EntityManagerInterface $entityManager;
    protected ParameterBagInterface $parameterBag;
    protected TranslatorInterface $translator;

    #[LiveAction]
    public function uploadFile(Request $request, PropertyAccessorInterface $propertyAccessor, #[LiveArg] string $name)
    {
        $propertyPath = $this->fieldNameToPropertyPath($name, $this->formName);
        $data = $propertyAccessor->getValue($this->formValues, $propertyPath);

        preg_match_all(
            '/\w+/',
            str_replace("file_input_", "", $name),
            $matches
        );
        $nameParts = $matches[0];
        $firstPart = array_shift($nameParts);
        $uploadedFiles = $request->files->get("live_file_input_" . $this->formName);
        foreach ($nameParts as $namePart) {
            $uploadedFiles = $uploadedFiles[$namePart];
        }
        try {
            if (!is_array($uploadedFiles)) {
                $uploadedFiles = [$uploadedFiles];
            }
            foreach ($uploadedFiles as $uploadedFile) {
                $file = new File();
                File::saveUploadedFile(
                    $file,
                    $uploadedFile,
                    $this->slugger,
                    $this->entityManager,
                    nameParts: [$firstPart, ...$nameParts]
                );
                if (is_array($data)) {
                    $data[] = $file->getId();
                } else {
                    $data = $file->getId();
                }
            }
            $propertyAccessor->setValue($this->formValues, $propertyPath, $data);
        } catch (\Exception $ex) {
            $this->error = $ex->getMessage();
        }
        $this->dispatchBrowserEvent('live-component:update');
    }

    #[LiveAction]
    public function removeFile(
        PropertyAccessorInterface $propertyAccessor,
        #[LiveArg] string $name,
        #[LiveArg] int $index
    ) {
        $propertyPath = $this->fieldNameToPropertyPath($name, $this->formName);
        $data = $propertyAccessor->getValue($this->formValues, $propertyPath);
        $file = null;
        if (is_array($data)) {
            $file = $this->fileRepository->find($data[$index]);
            unset($data[$index]);
            $propertyAccessor->setValue($this->formValues, $propertyPath, $data);
        } else {
            $file = $this->fileRepository->find($data);
            $propertyAccessor->setValue($this->formValues, $propertyPath, null);
        }

        if ($file) {
            $file->setStatus(FileStatus::Deleted);
            $this->entityManager->persist($file);
            $this->entityManager->flush();
        }
        $this->dispatchBrowserEvent('live-component:update');
    }

    private function fieldNameToPropertyPath(string $collectionFieldName, string $rootFormName): string
    {
        $propertyPath = $collectionFieldName;

        if (str_starts_with($collectionFieldName, $rootFormName)) {
            $propertyPath = substr_replace($collectionFieldName, '', 0, mb_strlen($rootFormName));
        }

        if (!str_starts_with($propertyPath, '[')) {
            $propertyPath = "[$propertyPath]";
        }

        return $propertyPath;
    }
}
