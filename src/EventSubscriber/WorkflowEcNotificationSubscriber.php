<?php

namespace App\EventSubscriber;

use App\Entity\Formation;
use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowEcNotificationSubscriber implements EventSubscriberInterface
{

    public function __construct(
        protected NotificationRepository $notificationRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.ec.transition.initialiser' => 'onInitialise',

        ];
    }

    public function onInitialise(Event $event): void
    {
        /** @var \App\Entity\ElementConstitutif $ec */
        $ec = $event->getSubject();

        if ($ec->getResponsableEc() !== null) {
            $notification = new Notification();
            $notification->setDestinataire($ec->getResponsableEc());
            $notification->setCodeNotification('workflow.ec.transition.initialiser');
            $this->notificationRepository->save($notification, true);
        }

    }

}
