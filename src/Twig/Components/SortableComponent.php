<?php

namespace App\Twig\Components;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\PostHydrate;
use Symfony\UX\LiveComponent\Attribute\PreDehydrate;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class SortableComponent extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp()]
    public ?string $class = null;

    #[LiveProp()]
    public ?string $objectName = null;

    #[LiveProp()]
    public ?string $formComponent = null;

    private EntityRepository $entityRepository;

    public function __construct(
        protected EntityManagerInterface $entityManager,
    ) {
    }

    #[PreDehydrate()]
    #[PostHydrate()]
    public function postHydrate()
    {
        $this->entityRepository = $this->entityManager->getRepository($this->class);
    }

    public function getItems()
    {
        return $this->entityRepository->findBy([], ['position' => 'ASC']);
    }

    #[LiveAction]
    public function saveOrder(
        #[LiveArg()] $itemId,
        #[LiveArg()] int $newPosition
    ) {
        $item = $this->entityRepository->find($itemId);
        if (!$item) {
            throw $this->createNotFoundException();
        }
        $item->setPosition($newPosition);
        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }

    #[LiveAction]
    public function removeItem(
        #[LiveArg()] $itemId
    ) {
        $item = $this->entityRepository->find($itemId);
        if (!$item) {
            throw $this->createNotFoundException();
        }
        $this->entityManager->remove($item);
        $this->entityManager->flush();
        $this->addFlash('success', 'Object removed.');
    }

    #[LiveListener('reloadSortableForm')]
    public function onReloadSortableForm()
    {
    }
}
