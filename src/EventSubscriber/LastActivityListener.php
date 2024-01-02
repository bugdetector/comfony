<?php

namespace App\EventSubscriber;

use App\Entity\Auth\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class LastActivityListener
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[AsEventListener(KernelEvents::REQUEST)]
    public function onResponse()
    {
        /** @var User */
        $user = $this->security?->getToken()?->getUser();
        if ($user) {
            $user->setLastAccess(new DateTime());
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }
}
