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
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.dpe.transition.refuser_definitif_cfvu' => 'onRefuseCfvu',
            'workflow.dpe.transition.refuser_revoir_cfvu' => 'onRefuseCfvu',
            'workflow.dpe.transition.valider_reserve_cfvu' => 'onValideCfvu',
            'workflow.dpe.transition.valider_reserve_conseil_cfvu' => 'onValideCfvu',
            'workflow.dpe.transition.valider_reserve_central_cfvu' => 'onValideCfvu',
            'workflow.dpe.transition.valider_cfvu' => 'onValideCfvu',
        ];
    }

    public function onRefuseCfvu(Event $event): void
    {
        //mail au RF
        /** @var Formation $formation */
        $formation = $event->getSubject();
        $dpe = $formation->getComposantePorteuse()?->getResponsableDpe();
        //todo: check si le responsable de formation accepte le mail
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/formation/refuse_cfvu.html.twig',
            [
                'dpe' => $dpe,
                'formation' => $formation,
                'context' => $event->getContext(),
            ]
        );
        $this->myMailer->sendMessage(
            [
                $dpe?->getEmail(),
                $formation->getResponsableMention()?->getEmail(),
                $formation->getCoResponsable()?->getEmail()],
            '[ORéOF]  Votre formation a été refusée par la CFVU'
        );
    }

    public function onValideCfvu(Event $event): void
    {
        //mail au RF
        /** @var Formation $formation */
        $formation = $event->getSubject();
        $dpe = $formation->getComposantePorteuse()?->getResponsableDpe();
        //todo: check si le responsable de formation accepte le mail
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/formation/valide_cfvu.html.twig',
            [
                'dpe' => $dpe,
                'formation' => $formation,
                'context' => $event->getContext(),
            ]
        );
        $this->myMailer->sendMessage(
            [
                $dpe?->getEmail(),
                $formation->getResponsableMention()?->getEmail(),
                $formation->getCoResponsable()?->getEmail()],
            '[ORéOF]  Votre formation a été validée par la CFVU'
        );
    }
}
