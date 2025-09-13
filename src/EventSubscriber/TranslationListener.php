<?php

namespace App\EventSubscriber;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Contracts\Translation\TranslatorInterface;

/** @property Translator $translator */
class TranslationListener
{
    public function __construct(
        private TranslatorInterface $translator,
        private LocaleSwitcher $localeSwitcher,
        private string $locales,
    ) {
    }

    #[AsEventListener(KernelEvents::REQUEST)]
    public function onRequest(KernelEvent $event)
    {
        $request = $event->getRequest();

        $session = $request->hasSession() ? $request->getSession() : null;
        $locale = $request->query->get('_locale');

        if ($locale) {
            if ($session) {
                $session->set('_locale', $locale);
            }
            $this->localeSwitcher->setLocale($locale);
            return;
        }

        // Restore locale from session if available
        if ($session && $session->has('_locale')) {
            $sessionLocale = $session->get('_locale');
            if ($sessionLocale) {
                $this->localeSwitcher->setLocale($sessionLocale);
                return;
            }
        }

        // Use browser preferred language
        $preferredLanguage = $request->getPreferredLanguage(explode('|', $this->locales));
        if ($preferredLanguage) {
            $this->localeSwitcher->setLocale($preferredLanguage);
        }
    }
}
