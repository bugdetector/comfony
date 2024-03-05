<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Mercure\Discovery;

class MercureCookieListener
{
    public function __construct(
        private Discovery $discovery,
        private Authorization $authorization
    ) {
    }

    #[AsEventListener(event: KernelEvents::REQUEST)]
    public function setCookie(RequestEvent $requestEvent)
    {
        // Check if it is a rest api request
        if (!$requestEvent->getRequest()->headers->get('authorization')) {
            $this->discovery->addLink($requestEvent->getRequest());
            $this->authorization->setCookie($requestEvent->getRequest(), ['*']);
        }
    }
}
