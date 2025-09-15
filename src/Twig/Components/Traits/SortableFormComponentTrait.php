<?php

namespace App\Twig\Components\Traits;

use Doctrine\ORM\Cache\Exception\FeatureNotImplemented;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;

trait SortableFormComponentTrait
{
    protected EntityManagerInterface $entityManager;

    #[LiveListener('setFormData')]
    public function onSetFormData(
        #[LiveArg()] ?string $objectId,
    ) {
        $entityClass = $this->getEntityClass();
        $repository = $this->entityManager->getRepository($entityClass);
        $this->initialFormData = $objectId ? $repository->find($objectId) : new $entityClass();
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
