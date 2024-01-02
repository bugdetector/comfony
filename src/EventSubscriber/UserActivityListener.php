<?php

namespace App\EventSubscriber;

use App\Entity\Auth\User;
use App\Entity\Auth\UserStatus;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserActivityListener
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator
    ) {
    }

    #[AsEventListener(KernelEvents::REQUEST)]
    public function onResponse(KernelEvent $event)
    {
        /** @var User */
        $user = $this->security?->getUser();
        if ($user) {
            $user->setLastAccess(new DateTime());
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            if ($user->getStatus() != UserStatus::Active) {
                $this->security->logout(false);
                /** @var Session */
                $session = $event->getRequest()->getSession();
                if ($user->getStatus() == UserStatus::Blocked) {
                    $session->getFlashBag()->add(
                        'error',
                        $this->translator->trans(
                            'Your account has been blocked. Please login after reset password.'
                        )
                    );
                } elseif ($user->getStatus() == UserStatus::Banned) {
                    $session->getFlashBag()->add(
                        'error',
                        $this->translator->trans(
                            'This account has been banned. You are not able to login with this account.'
                        )
                    );
                }
                $event->stopPropagation();
            }
        }
    }
}
