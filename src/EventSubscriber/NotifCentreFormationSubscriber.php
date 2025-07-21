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

class NotifCentreFormationSubscriber extends AbstractNotifCentreSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            NotifCentreFormationEvent::NOTIF_ADD_CENTRE => 'onAddCentreFormation',
            NotifCentreFormationEvent::NOTIF_REMOVE_CENTRE => 'onRemoveCentreFormation',
            NotifCentreFormationEvent::NOTIF_UPDATE_CENTRE => 'onUpdateCentreFormation',
        ];
    }

    public function onAddCentreFormation(NotifCentreFormationEvent $event): void
    {
        $this->sendNotification(
            $event->user,
            $event->formation,
            $event->profil,
            'mails/formation/add_centre_formation.html.twig',
            '[ORéOF] Accès à l\'application'
        );
    }

    public function onUpdateCentreFormation(NotifCentreFormationEvent $event): void
    {
        $this->sendNotification(
            $event->user,
            $event->formation,
            $event->profil,
            'mails/formation/update_centre_formation.html.twig',
            '[ORéOF] Modification de vos accès à l\'application'
        );
    }

    public function onRemoveCentreFormation(NotifCentreFormationEvent $event): void
    {
        $this->sendNotification(
            $event->user,
            $event->formation,
            $event->profil,
            'mails/formation/remove_centre_formation.html.twig',
            '[ORéOF] Accès à l\'application'
        );
    }
}
