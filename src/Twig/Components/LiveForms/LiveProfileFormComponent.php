<?php

namespace App\Twig\Components\LiveForms;

use App\Entity\Auth\User;
use App\Form\Auth\ProfileType;
use App\Twig\Components\FormType\LiveAsyncFileInputTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class LiveProfileFormComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use LiveAsyncFileInputTrait;

    #[LiveProp()]
    public ?User $initialFormData = null;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected TranslatorInterface $translator,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        $this->initialFormData = $this->getUser();
        $form = $this->createForm(ProfileType::class, $this->initialFormData, [
            'attr' => [
                'data-action' => 'live#action:prevent',
                'data-live-action-param' => 'save',
                'novalidate' => true
            ]
        ]);
        return $form;
    }

    #[LiveAction]
    public function save(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->submitForm();
        /** @var User */
        $object = $this->getForm()->getData();
        $password = $this->getForm()->get('plainPassword')->getData();
        if ($password) {
            $object->setPassword(
                $userPasswordHasher->hashPassword($object, $password)
            );
        }
        $this->entityManager->persist($object);
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans('Profile updated successfully'));
    }
}
