<?php

namespace App\Twig\Components\LiveForms;

use App\Entity\Page\Page;
use App\Form\Page\PageFormType;
use App\Repository\FileRepository;
use App\Twig\Components\FormType\LiveAsyncFileInputTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class LivePageFormComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use ComponentToolsTrait;
    use LiveAsyncFileInputTrait;

    #[LiveProp()]
    public ?Page $initialFormData = null;

    public function __construct(
        protected FileRepository $fileRepository,
        protected EntityManagerInterface $entityManager,
        protected TranslatorInterface $translator,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        if (!$this->initialFormData) {
            $this->initialFormData = new Page();
        }
        $form = $this->createForm(PageFormType::class, $this->initialFormData, [
            'attr' => [
                'data-action' => 'live#action:prevent',
                'data-live-action-param' => 'save',
                'novalidate' => true
            ]
        ]);
        return $form;
    }

    #[LiveAction]
    public function save(EntityManagerInterface $entityManager)
    {
        $this->submitForm();
        /** @var Position */
        $object = $this->getForm()->getData();
        $entityManager->persist($object);
        $entityManager->flush();

        $this->addFlash('success', $this->translator->trans(
            $this->initialFormData ? 'page.updated_successfully' : 'page.created_successfully'
        ));
    }
}
