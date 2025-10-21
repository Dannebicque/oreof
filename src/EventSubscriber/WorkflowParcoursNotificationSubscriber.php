<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/EventSubscriber/WorkflowDpeNotificationSubscriber.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\EventSubscriber;

use App\Entity\Formation;
use App\Entity\Notification;
use App\Events\ParcoursEvent;
use App\Repository\NotificationRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowParcoursNotificationSubscriber implements EventSubscriberInterface
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
