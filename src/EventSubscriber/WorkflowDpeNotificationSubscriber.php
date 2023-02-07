<?php

namespace App\EventSubscriber;

use App\Classes\Mailer;
use App\Entity\Formation;
use App\Entity\Notification;
use App\Repository\ComposanteRepository;
use App\Repository\FormationRepository;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowDpeNotificationSubscriber implements EventSubscriberInterface
{

    public function __construct(
        protected NotificationRepository $notificationRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.dpe.transition.initialiser' => 'onInitialise',

        ];
    }

    public function onInitialise(Event $event): void
    {
        /** @var Formation $formation */
        $formation = $event->getSubject();

        $notification = new Notification();
        $notification->setDestinataire($formation->getResponsableMention());
        $notification->setCodeNotification('workflow.dpe.transition.initialiser');
        $this->notificationRepository->save($notification, true);

    }

}
