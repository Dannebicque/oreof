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

class DpeCentralMailSubscriber extends AbstractDpeMailSubscriber implements EventSubscriberInterface
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
            'workflow.dpe.transition.refuser_central' => 'onRefuseCentral',
            'workflow.dpe.transition.valider_central' => 'onValideCentral',
            'workflow.dpe.transition.transmettre_cfvu' => 'onValideCentral',
            'workflow.dpe.transition.reserver_central' => 'onReserveCentral',
        ];
    }

    public function onRefuseCentral(Event $event): void
    {
        //mail au RF
        /** @var Formation $formation */
        $formation = $event->getSubject();
        $dpe = $formation->getComposantePorteuse()?->getResponsableDpe();
        $context = $event->getContext();

        //todo: check si le responsable de formation accepte le mail
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/formation/refuse_central.html.twig',
            [
                'dpe' => $dpe,
                'formation' => $formation,
                'motif' => $context['motif']]
        );
        $this->myMailer->sendMessage(
            [
                $dpe->getEmail(),
                $formation->getResponsableMention()?->getEmail(),
                $formation->getCoResponsable()?->getEmail()],
            '[ORéOF]  Votre formation a été refusée par le central',
            ['replyTo' => 'oreof-vp@univ-reims.fr', 'cc' => Mailer::MAIL_GENERIC]
        );
    }

    public function onValideCentral(Event $event): void
    {
        //todo: prevenir CFVU?
        /** @var Formation $formation */
        $formation = $event->getSubject();
        $dpe = $formation->getComposantePorteuse()?->getResponsableDpe();

        if ($formation === null) {
            return;
        }
        //todo: check si le responsable de formation accepte le mail

        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/formation/valide_central.html.twig',
            [
                'formation' => $formation,
                'dpe' => $dpe,
            ]
        );

        $this->myMailer->sendMessage(
            [
                $dpe->getEmail(),
                $formation->getResponsableMention()?->getEmail(),
                $formation->getCoResponsable()?->getEmail()],
            '[ORéOF]  La formation ' . $formation->getDisplayLong() . ' a été validé par le central'
        );
    }

    public function onReserveCentral(Event $event): void
    {
        //mail au RF
        /** @var Formation $formation */
        $formation = $event->getSubject();
        $dpe = $formation->getComposantePorteuse()?->getResponsableDpe();

        $context = $event->getContext();

        //todo: check si le responsable de formation accepte le mail
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/formation/reserve_central.html.twig',
            ['formation' => $formation,
                'dpe' => $dpe,
                'motif' => $context['motif']]
        );
        $this->myMailer->sendMessage(
            [
                $dpe->getEmail(),
                $formation->getResponsableMention()?->getEmail(),
                $formation->getCoResponsable()?->getEmail()],
            '[ORéOF]  Votre formation a reçu des réserves du central',
            ['replyTo' => 'oreof-vp@univ-reims.fr', 'cc' => Mailer::MAIL_GENERIC]
        );
    }
}
