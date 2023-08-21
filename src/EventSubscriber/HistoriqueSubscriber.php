<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/EventSubscriber/WorkflowDpeMailSubscriber.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\EventSubscriber;

use App\Classes\Mailer;
use App\Entity\Formation;
use App\Entity\HistoriqueFormation;
use App\Entity\HistoriqueParcours;
use App\Events\HistoriqueFormationEvent;
use App\Events\HistoriqueParcoursEvent;
use App\Repository\ComposanteRepository;
use App\Repository\FormationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class HistoriqueSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected UserRepository $userRepository,
        protected ComposanteRepository $composanteRepository,
        protected FormationRepository $formationRepository,
        protected EntityManagerInterface $entityManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            HistoriqueFormationEvent::ADD_HISTORIQUE_FORMATION => 'createHistoriqueFormation',
            HistoriqueParcoursEvent::ADD_HISTORIQUE_PARCOURS => 'createHistoriqueParcours',
//            'add.historique.parcours' => 'createHistoriqueParcours',
//            'add.historique.ficheEc' => 'createHistoriqueFicheEc',

        ];
    }


    public function createHistoriqueFormation(HistoriqueFormationEvent $event): void
    {
        $histo = new HistoriqueFormation();
        $histo->setFormation($event->getFormation());
        $histo->setCreated(new \DateTime());
        $histo->setUser($event->getUser());
        $histo->setEtape($event->getEtape());
        $histo->setCommentaire($event->getCommentaire());
        $histo->setEtat($event->getEtat());

        $this->entityManager->persist($histo);
        $this->entityManager->flush();

    }

    public function createHistoriqueParcours(HistoriqueParcoursEvent $event): void
    {
        $histo = new HistoriqueParcours();
        $histo->setParcours($event->getParcours());
        $histo->setCreated(new \DateTime());
        $histo->setUser($event->getUser());
        $histo->setEtape($event->getEtape());
        $histo->setCommentaire($event->getCommentaire());
        $histo->setEtat($event->getEtat());

        $this->entityManager->persist($histo);
        $this->entityManager->flush();

    }
}
