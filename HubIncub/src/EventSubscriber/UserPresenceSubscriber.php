<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class UserPresenceSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'updateLastSeenAt',
        ];
    }

    public function updateLastSeenAt(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return;
        }

        $now = new \DateTimeImmutable();
        $lastSeenAt = $user->getLastSeenAt();

        if ($lastSeenAt && $lastSeenAt >= $now->modify('-60 seconds')) {
            return;
        }

        $user->setLastSeenAt($now);
        $this->entityManager->flush();
    }
}
