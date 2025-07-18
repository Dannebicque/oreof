<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/EventSubscriber/DpeWorkflow/WorkflowDpeMailSubscriber.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 06/10/2023 08:31
 */

namespace App\EventSubscriber\DpeWorkflow;

use App\Classes\GetHistorique;
use App\Classes\Mailer;
use App\Entity\Formation;
use App\Repository\ComposanteRepository;
use App\Repository\FormationRepository;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class DpeCfvuMailSubscriber extends AbstractDpeMailSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected GetHistorique        $getHistorique,
        protected UserRepository       $userRepository,
        protected ComposanteRepository $composanteRepository,
        protected FormationRepository  $formationRepository,
        protected Mailer               $myMailer
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.dpeParcours.transition.refuser_definitif_cfvu' => 'onRefuseCfvu',
            'workflow.dpeParcours.transition.refuser_revoir_cfvu' => 'onReserveCfvu',
            'workflow.dpeParcours.transition.valider_reserve_cfvu' => 'onValideCfvu',
            'workflow.dpeParcours.transition.valider_reserve_conseil_cfvu' => 'onValideCfvu',
            'workflow.dpeParcours.transition.valider_reserve_central_cfvu' => 'onValideCfvu',
            'workflow.dpeParcours.transition.valider_cfvu' => 'onValideCfvu',
        ];
    }

    public function onReserveCfvu(Event $event): void
    {
        $data = $this->getDataFromEvent($event);
        if ($data === null) {
            return;
        }
        $context = $event->getContext();

        //todo: check si le responsable de formation accepte le mail
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/formation/reserve_cfvu.html.twig',
            array_merge($this->getData(), ['context' => $event->getContext()])
        );

        $titre = $this->hasParcours ?
            'Votre parcours ' . $this->parcours->getLibelle() . ' de la formation ' . $this->formation->getDisplay() . ' a reçu des réserves de la CFVU' :
            'Votre formation ' . $this->formation->getDisplay() . ' a reçu des réserves de la CFVU';

        $this->myMailer->sendMessage(
            $this->getDestinataires(true),
            '[ORéOF] ' . $titre
        );
    }


    public function onRefuseCfvu(Event $event): void
    {
        $data = $this->getDataFromEvent($event);
        if ($data === null) {
            return;
        }
        $context = $event->getContext();

        //todo: check si le responsable de formation accepte le mail
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/formation/refuse_cfvu.html.twig',
            array_merge($this->getData(), ['context' => $event->getContext()])
        );
        $this->myMailer->sendMessage(
            $this->getDestinataires(true),
            '[ORéOF]  Votre formation a été refusée par la CFVU'
        );
    }

    public function onValideCfvu(Event $event): void
    {
        $formation = $event->getSubject()->getParcours()->getFormation();
        $parcours = $event->getSubject()->getParcours();

        if ($parcours->isParcoursDefaut()) {
            $titre = 'Votre formation a été validée par la CFVU';
        } else {
            $titre = 'Un parcours de votre formation a été validé par la CFVU';
            //todo: gérer les destinataires parcours
        }

        $dpe = $formation->getComposantePorteuse()?->getResponsableDpe();
        //todo: check si le responsable de formation accepte le mail
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/formation/valide_cfvu.html.twig',
            [
                'dpe' => $dpe,
                'formation' => $formation,
                'parcours' => $parcours,
                'context' => $event->getContext(),
            ]
        );
        $this->myMailer->sendMessage(
            [
                $dpe?->getEmail(),
                $formation->getResponsableMention()?->getEmail(),
                $formation->getCoResponsable()?->getEmail()], //todo: ajouter le RP à chaque fois si parcours
            '[ORéOF]  '.$titre
        );
    }
}
