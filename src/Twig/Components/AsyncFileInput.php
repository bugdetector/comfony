<?php

namespace App\Twig\Components;

use App\Entity\File\File;
use App\Repository\FileRepository;
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
            $file = new File();
            File::saveUploadedFile(
                $file,
                $uploadedFiles,
                $this->slugger,
                $this->entityManager,
                $this->parameterBag->get('uploads_directory'),
                nameParts: [$firstPart, ...$nameParts]
            );
            $this->vars["value"] = $file->getId();
        } catch (Exception $ex) {
            $message = $ex->getMessage();
        }
    }

    public function hydrateVars($vars)
    {
        return json_decode($vars, true);
    }

    public function dehydrateVars($data)
    {
        $data = array_filter($data, function ($el) {
            return !is_object($el);
        });
        return json_encode($data);
    }

    public function getFile()
    {
        return $this->vars['value'] ? $this->fileRepository->find($this->vars['value']) : null;
    }

    public function getFullName()
    {
        return $this->vars['full_name'];
    }
}
