<?php

namespace App\Twig\Components;

use App\Entity\File\File;
use App\Entity\File\FileStatus;
use App\Repository\FileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(name: 'async_file_input')]
final class AsyncFileInput
{
    use DefaultActionTrait;

    #[LiveProp(hydrateWith: 'hydrateVars', dehydrateWith: 'dehydrateVars')]
    public $vars;

    public function __construct(
        private FileRepository $fileRepository,
        private SluggerInterface $slugger,
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface $parameterBag
    ) {
    }

    #[LiveAction]
    public function uploadFile(Request $request)
    {
        preg_match_all(
            '/\w+/',
            str_replace("file_input_", "", $this->vars['full_name']),
            $matches
        );
        $nameParts = $matches[0];
        $firstPart = array_shift($nameParts);
        $uploadedFiles = $request->files->get("file_input_" . $firstPart);
        foreach ($nameParts as $namePart) {
            $uploadedFiles = $uploadedFiles[$namePart];
        }
        try {
            if (isset($this->vars["attr"]["multiple"]) && $this->vars["attr"]["multiple"]) {
                foreach ($uploadedFiles as $uploadedFile) {
                    $file = new File();
                    File::saveUploadedFile(
                        $file,
                        $uploadedFile,
                        $this->slugger,
                        $this->entityManager,
                        nameParts: [$firstPart, ...$nameParts]
                    );
                    $this->vars["data"][] = $file;
                }
            } else {
                $file = new File();
                File::saveUploadedFile(
                    $file,
                    $uploadedFiles,
                    $this->slugger,
                    $this->entityManager,
                    nameParts: [$firstPart, ...$nameParts]
                );
                $this->vars["value"] = $file->getId();
            }
        } catch (Exception $ex) {
            $message = $ex->getMessage();
        }
    }

    #[LiveAction]
    public function removeFile(Request $request)
    {
        if ($file = $this->getFiles()[0]) {
            $file->setStatus(FileStatus::Temporary);
            $this->entityManager->persist($file);
            $this->entityManager->flush();
            $this->vars["value"] = null;
        }
    }

    public function hydrateVars($vars)
    {
        $vars = json_decode($vars, true);
        if (isset($vars["data"]) && is_array($vars["data"])) {
            $vars["data"] = new ArrayCollection($this->fileRepository->findBy([
                "id" => $vars["data"]
            ]));
        }
        return $vars;
    }

    public function dehydrateVars($data)
    {
        if (isset($data["data"]) && $data["data"] instanceof Collection) {
            $data["data"] = $data["data"]->map(function (File $file) {
                return $file->getId();
            })->toArray();
        }
        $data = array_filter($data, function ($el) {
            return !is_object($el);
        });
        return json_encode($data);
    }

    public function getFiles()
    {
        if (isset($this->vars["attr"]["multiple"]) && $this->vars["attr"]["multiple"]) {
            return $this->vars["data"];
        } else {
            return $this->vars['value'] ? [$this->fileRepository->find($this->vars['value'])] : [];
        }
    }

    public function getFullName()
    {
        return $this->vars['full_name'];
    }
}
