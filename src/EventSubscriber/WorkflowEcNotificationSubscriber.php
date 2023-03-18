<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/EventSubscriber/WorkflowEcNotificationSubscriber.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

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
