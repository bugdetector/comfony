<?php

namespace App\Twig\Components\LiveForms;

use App\Entity\Category\Category;
use App\Form\Category\CategoryFormType;
use App\Twig\Components\Traits\SortableFormComponentTrait;
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
final class LiveCategoryFormComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use SortableFormComponentTrait;
    use ComponentToolsTrait;

    #[LiveProp()]
    public ?Category $initialFormData = null;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected TranslatorInterface $translator,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        if (!$this->initialFormData) {
            $this->initialFormData = new Category();
        }
        $form = $this->createForm(CategoryFormType::class, $this->initialFormData, [
            'attr' => [
                'data-action' => 'live#action:prevent',
                'data-live-action-param' => 'save',
                'novalidate' => true
            ]
        ]);
        return $form;
    }

    #[LiveAction]
    public function save()
    {
        $this->submitForm();
        /** @var Category */
        $object = $this->getForm()->getData();
        $isCreate = !boolval($object->getId());
        $this->entityManager->persist($object);
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans(
            $isCreate ? 'Category created successfully.' : 'Category updated successfully.'
        ));
        $this->dispatchBrowserEvent('app-hide-modal');
    }

    private function getEntityClass(): string
    {
        return Category::class;
    }
}
