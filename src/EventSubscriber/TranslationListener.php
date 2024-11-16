<?php

namespace App\EventSubscriber;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

/** @property Translator $translator */
class TranslationListener
{
    public function __construct(
        private TranslatorInterface $translator
    ) {
    }

    #[AsEventListener(KernelEvents::REQUEST)]
    public function onRequest(KernelEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->hasPreviousSession()) {
            return;
        }
        // try to see if the locale has been set as a _locale routing parameter
        if ($locale = $request->query->get('_locale')) {
            $request->getSession()->set('_locale', $locale);
        }
        $locale = $request->getSession()->get('_locale');
        if ($locale) {
            $request->setLocale($locale);
            $this->translator->setLocale($locale);
        }
    }
}
