<?php

namespace App\Twig\Components\Traits;

use Doctrine\ORM\Cache\Exception\FeatureNotImplemented;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

trait SortableFormComponentTrait
{
    protected EntityManagerInterface $entityManager;

    #[LiveProp()]
    public ?string $parentId = null;

    #[LiveListener('setFormData')]
    public function onSetFormData(
        #[LiveArg()] ?string $objectId,
        #[LiveArg()] ?string $parentId = null,
    ) {
        $entityClass = $this->getEntityClass();
        $repository = $this->entityManager->getRepository($entityClass);
        $this->initialFormData = $objectId ? $repository->find($objectId) : new $entityClass();
        if ($parentId) {
            $parent = $repository->find($parentId);
            $this->initialFormData->setParent($parent);
        }
        $this->resetForm();
    }

    #[LiveListener('closeModal')]
    public function onCloseModal()
    {
        $this->initialFormData = null;
        $this->resetForm();
    }

    private function getEntityClass(): string
    {
        throw new FeatureNotImplemented('You should return entity class here.');
    }
}
