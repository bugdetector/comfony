<?php

namespace App\EventSubscriber;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Mercure\Discovery;

class MercureCookieListener
{
    public function __construct(
        private Security $security,
        private Discovery $discovery,
        private Authorization $authorization
    ) {
    }

    #[AsEventListener(event: KernelEvents::REQUEST)]
    public function setCookie(RequestEvent $requestEvent)
    {
        /** @var User */
        $user = $this->security?->getUser();
        // Check if user is logged in and it is not a rest api request
        if ($user && !$requestEvent->getRequest()->headers->get('authorization')) {
            $this->discovery->addLink($requestEvent->getRequest());
            $this->authorization->setCookie($requestEvent->getRequest(), ['*']);
        }
    }
}
