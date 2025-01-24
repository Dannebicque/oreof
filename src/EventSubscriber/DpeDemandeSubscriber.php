<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/EventSubscriber/DpeDemandeSubscriber.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 30/01/2024 08:55
 */

namespace App\EventSubscriber;

use App\Classes\Mailer;
use App\Events\DpeDemandeEvent;
use App\Repository\FormationRepository;
use App\Repository\UserCentreRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DpeDemandeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected Mailer               $mailer,
        protected FormationRepository  $formationRepository,
        protected UserCentreRepository $userCentreRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DpeDemandeEvent::DPE_DEMANDE_CREATED => 'onDpeDemandeCreated',
            DpeDemandeEvent::DPE_DEMANDE_UPDATED => 'onDpeDemandeUpdated',
        ];
    }

    public function onDpeDemandeCreated(DpeDemandeEvent $event): void
    {
        $dpeDemande = $event->getDpeDemande();
        $user = $event->getUser();
        $this->mailer->initEmail();
        $this->mailer->setTemplate(
            'mails/dpe_demande_created.txt.twig',
            ['dpeDemande' => $dpeDemande]
        );
        $this->mailer->sendMessage(['oreof@univ-reims.fr', $user->getEmail()], '[ORéOF] Demande de DPE créée');

        $this->mailer->initEmail();
        $this->mailer->setTemplate(
            'mails/dpe/dpe_demande_created_demandeur.txt.twig',
            ['dpeDemande' => $dpeDemande]
        );
        $this->mailer->sendMessage([$user->getEmail()], '[ORéOF] Demande de DPE créée');
    }

    public function onDpeDemandeUpdated(DpeDemandeEvent $event): void
    {
        $dpeDemande = $event->getDpeDemande();
        $user = $event->getUser();

        $this->mailer->initEmail();
        $this->mailer->setTemplate(
            'mails/dpe/dpe_demande_updated.txt.twig',
            ['dpeDemande' => $dpeDemande]
        );
        $this->mailer->sendMessage([$user->getEmail()], '[ORéOF] Mise à jour de la demande de DPE');
    }
}
