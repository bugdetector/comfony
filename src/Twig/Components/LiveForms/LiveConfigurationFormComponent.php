<?php

namespace App\Twig\Components\LiveForms;

use App\Entity\Configuration\Configuration;
use App\Form\Configuration\ConfigurationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class LiveConfigurationFormComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp()]
    public ?Configuration $initialFormData = null;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected TranslatorInterface $translator,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        if (!$this->initialFormData) {
            $this->initialFormData = new Configuration();
        }
        $form = $this->createForm(ConfigurationFormType::class, $this->initialFormData, [
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
        /** @var Configuration */
        $object = $this->getForm()->getData();
        $this->entityManager->persist($object);
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans('Configuration saved successfully.'));
    }
}
