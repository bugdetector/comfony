<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Mercure\Discovery;

class MercureCookieListener
{
    public function __construct(
        private Security $security,
        private Discovery $discovery,
        private Authorization $authorization,
        private LoggerInterface $logger
    ) {
    }

    #[AsEventListener(event: KernelEvents::RESPONSE)]
    public function setCookie(ResponseEvent $responseEvent)
    {
        /** @var User */
        $user = $this->security?->getUser();
        $request = $responseEvent->getRequest();
        // Check if user is logged in and it is not a rest api request
        if ($user && !$request->get('_stateless') && $request->hasSession()) {
            try {
                $this->discovery->addLink($request);
                $this->authorization->setCookie($request, ['*']);
            } catch (\Exception $ex) {
                $this->logger->error($ex->getMessage());
            }
        }
    }
}
