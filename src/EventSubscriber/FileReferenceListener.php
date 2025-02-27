<?php

namespace App\EventSubscriber;

use App\Entity\File\File;
use App\Entity\File\FileStatus;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class FileReferenceListener extends AbstractController implements EventSubscriber
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
        ];
    }

    #[AsEventListener(KernelEvents::REQUEST)]
    public function setEventListener(KernelEvent $event)
    {
        $this->entityManager->getEventManager()->addEventSubscriber($this);
    }

    public function postPersist(PostPersistEventArgs $args)
    {
        $object = $args->getObject();
        $this->checkFileReferences($object, 'create');
    }

    public function postUpdate(PostUpdateEventArgs $args)
    {
        $object = $args->getObject();
        $this->checkFileReferences($object, 'update');
    }

    private function checkFileReferences(object $object, string $action): void
    {
        if ($object instanceof File) {
            return;
        }
        $reflectionClass = new \ReflectionClass($object);
        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($object);
            if ($value instanceof Collection) {
                $value->forAll(function ($index, $object) {
                    if ($object instanceof File) {
                        $this->updateFileStatus($object);
                        return true;
                    }
                    return false;
                });
            } else {
                $this->updateFileStatus($object);
            }
        }
    }

    private function updateFileStatus($object)
    {
        if ($object instanceof File) {
            $object->setStatus(FileStatus::Permanent);
            $this->entityManager->persist($object);
            $this->entityManager->flush();
        }
    }
}
