<?php

namespace App\Twig\Components\FormType;

use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\TranslatableListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

trait TranslatableFormTrait
{
    protected EntityManagerInterface $entityManager;

    #[LiveProp(url: true, writable: true, onUpdated: 'onLocaleUpdated')]
    public ?string $locale = null;

    public string $defaultLocale;

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
