<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/EventSubscriber/UserAccesSubscriber.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\EventSubscriber;

use App\Events\NotifCentreEtablissementEvent;

class NotifCentreEtablissementSubscriber extends AbstractNotifCentreSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            NotifCentreEtablissementEvent::NOTIF_ADD_CENTRE => 'onAddCentreEtablissement',
            NotifCentreEtablissementEvent::NOTIF_REMOVE_CENTRE => 'onRemoveCentreEtablissement',
        ];
    }

    public function onAddCentreEtablissement(NotifCentreEtablissementEvent $event): void
    {
        $this->sendNotification(
            $event->user,
            $event->etablissement,
            $event->profil,
            'mails/etablissement/add_centre_etablissement.html.twig',
            '[ORéOF] Accès à l\'application'
        );
    }

    public function onRemoveCentreEtablissement(NotifCentreEtablissementEvent $event): void
    {
        $this->sendNotification(
            $event->user,
            $event->etablissement,
            $event->profil,
            'mails/etablissement/remove_centre_etablissement.html.twig',
            '[ORéOF] Accès à l\'application'
        );
    }
}
