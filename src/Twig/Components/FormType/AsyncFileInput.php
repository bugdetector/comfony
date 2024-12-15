<?php

namespace App\Twig\Components\FormType;

use App\Entity\File\File;
use App\Entity\File\FileStatus;
use App\Repository\FileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent()]
final class AsyncFileInput
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp(hydrateWith: 'hydrateVars', dehydrateWith: 'dehydrateVars')]
    public $vars;

    public $error = null;

    public function __construct(
        private FileRepository $fileRepository,
        private SluggerInterface $slugger,
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface $parameterBag,
        private TranslatorInterface $translator
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
                /** @var UploadedFile $uploadedFile */
                foreach ($uploadedFiles as $uploadedFile) {
                    if (!$uploadedFile->getError()) {
                        $file = new File();
                        File::saveUploadedFile(
                            $file,
                            $uploadedFile,
                            $this->slugger,
                            $this->entityManager,
                            nameParts: [$firstPart, ...$nameParts]
                        );
                        $this->vars["data"][] = $file;
                    } else {
                        $this->error = $this->translator->trans(
                            "File upload size exceed. Maximum size: { maxSize }",
                            [
                                "maxSize" => ini_get('upload_max_filesize')
                            ]
                        );
                    }
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
            $this->error = $ex->getMessage();
        }
        $this->dispatchBrowserEvent('live-component:update');
    }

    #[LiveAction]
    public function removeFile(#[LiveArg] int $index)
    {
        if ($file = $this->getFiles()[$index]) {
            $file->setStatus(FileStatus::Deleted);
            $this->entityManager->persist($file);
            $this->entityManager->flush();
            if (isset($this->vars["attr"]["multiple"]) && $this->vars["attr"]["multiple"]) {
                unset($this->vars["data"][$index]);
                $this->vars["data"] = new ArrayCollection(
                    $this->vars["data"]->getValues()
                );
            } else {
                $this->vars["value"] = null;
            }
        }
        $this->dispatchBrowserEvent('live-component:update');
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
