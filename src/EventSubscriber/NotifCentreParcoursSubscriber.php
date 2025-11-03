<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/EventSubscriber/UserAccesSubscriber.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\EventSubscriber;

use App\Events\NotifCentreFormationEvent;
use App\Events\NotifCentreParcoursEvent;

class NotifCentreParcoursSubscriber extends AbstractNotifCentreSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            NotifCentreParcoursEvent::NOTIF_ADD_CENTRE => 'onAddCentreParcours',
            NotifCentreParcoursEvent::NOTIF_REMOVE_CENTRE => 'onRemoveCentreParcours'
        ];
    }

    public function onAddCentreParcours(NotifCentreParcoursEvent $event): void
    {
        $this->sendNotification(
            $event->user,
            $event->parcours,
            $event->profil,
            'mails/parcours/add_centre_parcours.html.twig',
            '[ORéOF] Accès à l\'application'
        );
    }

    public function onUpdateCentreFormation(NotifCentreParcoursEvent $event): void
    {
        $this->sendNotification(
            $event->user,
            $event->parcours,
            $event->profil,
            'mails/parcours/update_centre_parcours.html.twig',
            '[ORéOF] Modification de vos accès à l\'application'
        );
    }

    public function onRemoveCentreParcours(NotifCentreParcoursEvent $event): void
    {
        $this->sendNotification(
            $event->user,
            $event->parcours,
            $event->profil,
            'mails/parcours/remove_centre_parcours.html.twig',
            '[ORéOF] Accès à l\'application'
        );
    }
}
