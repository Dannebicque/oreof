<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/EventSubscriber/UserAccesSubscriber.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\EventSubscriber;

use App\Classes\Mailer;
use App\Events\NotifCentreEtablissementEvent;
use App\Repository\EtablissementRepository;
use App\Repository\UserCentreRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotifCentreEtablissementSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected Mailer $mailer,
        protected EtablissementRepository $etablissementRepository,
        protected UserCentreRepository $userCentreRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NotifCentreEtablissementEvent::NOTIF_ADD_CENTRE_ETABLISSEMENT => 'onAddCentreEtablissement',
            NotifCentreEtablissementEvent::NOTIF_REMOVE_CENTRE_ETABLISSEMENT => 'onRemoveCentreEtablissement',
        ];
    }

    public function onAddCentreEtablissement(NotifCentreEtablissementEvent $event): void
    {
        $user = $event->user;
        $etablissement = $event->etablissement;

        if ($user === null || $etablissement === null) {
            return;
        }

        $this->mailer->initEmail();
        $this->mailer->setTemplate(
            'mails/etablissement/add_centre_etablissement.txt.twig',
            ['user' => $user, 'etablissement' => $etablissement]
        );
        $this->mailer->sendMessage([$user->getEmail()], '[ORéOF] Accès à l\'application');
    }

    public function onRemoveCentreEtablissement(NotifCentreEtablissementEvent $event)
    {
        $user = $event->user;
        $etablissement = $event->etablissement;

        if ($user === null || $etablissement === null) {
            return;
        }

        $this->mailer->initEmail();
        $this->mailer->setTemplate(
            'mails/etablissement/remove_centre_etablissement.txt.twig',
            ['user' => $user, 'etablissement' => $etablissement]
        );
        $this->mailer->sendMessage([$user->getEmail()], '[ORéOF] Accès à l\'application');
    }
}
