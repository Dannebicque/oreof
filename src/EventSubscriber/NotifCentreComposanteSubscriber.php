<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/EventSubscriber/UserAccesSubscriber.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\EventSubscriber;

use App\Events\NotifCentreComposanteEvent;

class NotifCentreComposanteSubscriber extends AbstractNotifCentreSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            NotifCentreComposanteEvent::NOTIF_ADD_CENTRE => 'onAddCentreComposante',
            NotifCentreComposanteEvent::NOTIF_REMOVE_CENTRE => 'onRemoveCentreComposante',
        ];
    }

    public function onAddCentreComposante(NotifCentreComposanteEvent $event): void
    {
        $this->sendNotification(
            $event->user,
            $event->composante,
            $event->profil,
            'mails/composante/add_centre_composante.html.twig',
            '[ORéOF] Accès à l\'application'
        );
    }

    public function onRemoveCentreComposante(NotifCentreComposanteEvent $event): void
    {
        $this->sendNotification(
            $event->user,
            $event->composante,
            $event->profil,
            'mails/composante/remove_centre_composante.html.twig',
            '[ORéOF] Accès à l\'application'
        );
    }
}
