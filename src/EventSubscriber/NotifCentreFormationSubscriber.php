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
use App\Events\NotifCentreFormationEvent;
use App\Repository\FormationRepository;
use App\Repository\UserCentreRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotifCentreFormationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected Mailer $mailer,
        protected FormationRepository $formationRepository,
        protected UserCentreRepository $userCentreRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NotifCentreFormationEvent::NOTIF_ADD_CENTRE_FORMATION => 'onAddCentreFormation',
            NotifCentreFormationEvent::NOTIF_REMOVE_CENTRE_FORMATION => 'onRemoveCentreFormation',
        ];
    }

    public function onAddCentreFormation(NotifCentreFormationEvent $event): void
    {
        $user = $event->user;
        $formation = $event->formation;

        if (($user === null) || ($formation === null)) {
            return;
        }

        $this->mailer->initEmail();
        $this->mailer->setTemplate(
            'mails/formation/add_centre_formation.txt.twig',
            ['user' => $user, 'formation' => $formation]
        );
        $this->mailer->sendMessage([$user->getEmail()], '[ORéOF] Accès à l\'application');
    }

    public function onRemoveCentreFormation(NotifCentreFormationEvent $event): void
    {
        $user = $event->user;
        $formation = $event->formation;

        if (($user === null) || ($formation === null)) {
            return;
        }

        $this->mailer->initEmail();
        $this->mailer->setTemplate(
            'mails/formation/remove_centre_formation.txt.twig',
            ['user' => $user, 'formation' => $formation]
        );
        $this->mailer->sendMessage([$user->getEmail()], '[ORéOF] Accès à l\'application');
    }
}
