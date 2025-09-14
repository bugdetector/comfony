<?php

namespace App\Twig\Components\LiveForms;

use App\Entity\Page\Page;
use App\Form\Page\PageFormType;
use App\Twig\Components\FormType\LiveAsyncFileInputTrait;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class LivePageFormComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use LiveAsyncFileInputTrait;

    #[LiveProp()]
    public ?Page $initialFormData = null;

    #[LiveProp(url: true, writable: true, onUpdated: 'onLocaleUpdated')]
    public ?string $locale = null;

    public string $defaultLocale;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected TranslatorInterface $translator,
        string $locale,
    ) {
        $this->defaultLocale = $locale;
        $this->locale = $locale;
    }

    protected function instantiateForm(): FormInterface
    {
        if (!$this->initialFormData) {
            $this->initialFormData = new Page();
        } elseif ($this->locale && $this->initialFormData->getId()) {
            $this->getTranslatableListener()->setTranslatableLocale($this->locale);
            $this->entityManager->refresh($this->initialFormData);
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
    public function save()
    {
        $this->submitForm();
        /** @var Page */
        $object = $this->getForm()->getData();
        $object->setTranslatableLocale($this->locale);
        $isCreate = !boolval($object->getId());
        $this->entityManager->persist($object);
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans(
            $isCreate ? 'Page created successfully.' : 'Page updated successfully.'
        ));
        if ($isCreate) {
            return $this->redirectToRoute('app_admin_page_edit', ['id' => $object->getId()]);
        }
    }

    private function getTranslatableListener(): ?TranslatableListener
    {
        foreach ($this->entityManager->getEventManager()->getAllListeners() as $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof TranslatableListener) {
                    return $listener;
                }
            }
        }
        return null;
    }

    public function onLocaleUpdated()
    {
        $this->resetForm();
    }
}
