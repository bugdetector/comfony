<?php

namespace App\Twig\Components;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
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
        private TranslatorInterface $translator,
    ) {
    }

    #[PreDehydrate()]
    #[PostHydrate()]
    public function postHydrate()
    {
        if (!in_array('App\Entity\Trait\SortableEntity', class_uses($this->class))) {
            throw new \LogicException(sprintf('Class %s must use App\Trait\SortableEntity trait.', $this->class));
        }
        $this->entityRepository = $this->entityManager->getRepository($this->class);
    }

    public function getItems()
    {
        if ($this->isTreeEntity()) {
            // Return only root nodes, sorted by position
            return $this->entityRepository->findBy(['parent' => null], ['position' => 'ASC']);
        }
        return $this->entityRepository->findBy([], ['position' => 'ASC']);
    }

    #[LiveAction]
    public function saveOrder(
        #[LiveArg()] $itemId,
        #[LiveArg()] int $newPosition,
        #[LiveArg()] $parentId = null
    ) {
        $item = $this->entityRepository->find($itemId);
        if (!$item) {
            throw $this->createNotFoundException();
        }
        if ($this->isTreeEntity()) {
            $parent = $parentId ? $this->entityRepository->find($parentId) : null;
            $item->setParent($parent);
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
        $this->addFlash('success', $this->translator->trans($this->objectName . ' deleted successfully.'));
    }

    #[LiveListener('reloadSortableForm')]
    public function onReloadSortableForm()
    {
    }

    public function isTreeEntity()
    {
        return in_array('App\Entity\Trait\TreeEntity', class_uses($this->class));
    }
}
