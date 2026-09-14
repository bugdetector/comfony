<?php

namespace App\EventSubscriber;

use App\Entity\File\File;
use App\Entity\File\FileStatus;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class FileReferenceListener implements EventSubscriber
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
        ];
    }

    #[AsEventListener(KernelEvents::REQUEST)]
    public function setEventListener(KernelEvent $event): void
    {
        $this->entityManager->getEventManager()->addEventSubscriber($this);
    }

    #[AsEventListener(ConsoleEvents::COMMAND)]
    public function setEventListenerForConsole(ConsoleCommandEvent $event): void
    {
        $this->entityManager->getEventManager()->addEventSubscriber($this);
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $args->getObjectManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityInsertions() ?? [] as $object) {
            $this->checkFileReferences($object, FileStatus::Permanent, $entityManager, $unitOfWork);
        }

        foreach ($unitOfWork->getScheduledEntityUpdates() ?? [] as $object) {
            $this->checkFileReferences($object, FileStatus::Permanent, $entityManager, $unitOfWork);
            $this->checkRemovedFileReferences($object, $entityManager, $unitOfWork);
        }

        foreach ($unitOfWork->getScheduledCollectionUpdates() ?? [] as $collection) {
            foreach ($collection->getDeleteDiff() as $element) {
                if ($element instanceof File) {
                    $this->updateFileStatus($element, FileStatus::Deleted, $entityManager, $unitOfWork);
                }
            }
        }

        foreach ($unitOfWork->getScheduledCollectionDeletions() ?? [] as $collection) {
            foreach ($collection as $element) {
                if ($element instanceof File) {
                    $this->updateFileStatus($element, FileStatus::Deleted, $entityManager, $unitOfWork);
                }
            }
        }

        foreach ($unitOfWork->getScheduledEntityDeletions() ?? [] as $object) {
            $this->checkFileReferences($object, FileStatus::Deleted, $entityManager, $unitOfWork);
        }
    }

    private function checkRemovedFileReferences(
        object $object,
        EntityManagerInterface $entityManager,
        UnitOfWork $unitOfWork,
    ): void {
        if ($object instanceof File) {
            return;
        }

        $changeSet = $unitOfWork->getEntityChangeSet($object) ?? [];
        foreach ($changeSet as $change) {
            [$oldValue, $newValue] = $change;

            if (($oldValue instanceof File) && $oldValue !== $newValue) {
                $this->updateFileStatus($oldValue, FileStatus::Deleted, $entityManager, $unitOfWork);
            }
        }
    }

    private function checkFileReferences(
        object $object,
        FileStatus $newStatus,
        EntityManagerInterface $entityManager,
        UnitOfWork $unitOfWork,
    ): void {
        if ($object instanceof File) {
            return;
        }

        $reflectionClass = new \ReflectionClass($object);
        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            if (!$property->isInitialized($object)) {
                continue;
            }

            $value = $property->getValue($object);
            if ($value instanceof Collection) {
                $value->forAll(function ($index, $item) use ($newStatus, $entityManager, $unitOfWork) {
                    if ($item instanceof File) {
                        $this->updateFileStatus($item, $newStatus, $entityManager, $unitOfWork);
                        return true;
                    }
                    return false;
                });
            } else {
                $this->updateFileStatus($value, $newStatus, $entityManager, $unitOfWork);
            }
        }
    }

    private function updateFileStatus(
        mixed $object,
        FileStatus $newStatus,
        EntityManagerInterface $entityManager,
        UnitOfWork $unitOfWork,
    ): void {
        if (!$object instanceof File) {
            return;
        }

        if ($object->getStatus() === $newStatus->value || $unitOfWork->isScheduledForDelete($object)) {
            return;
        }

        $object
            ->setStatus($newStatus)
            ->setUpdatedAt(new \DateTime());

        $unitOfWork->recomputeSingleEntityChangeSet(
            $entityManager->getClassMetadata(File::class),
            $object,
        );
    }
}
