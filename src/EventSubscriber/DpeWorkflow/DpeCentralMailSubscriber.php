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
            'workflow.dpeParcours.transition.refuser_central' => 'onRefuseCentral',
            'workflow.dpeParcours.transition.valider_central' => 'onValideCentral',
            'workflow.dpeParcours.transition.transmettre_cfvu' => 'onValideCentral',
            'workflow.dpeParcours.transition.valider_transmettre_ouverture_sans_cfvu' => 'onValideCentralSansCfvu',
            'workflow.dpeParcours.transition.reserver_central' => 'onReserveCentral',
            'workflow.dpeParcours.transition.reserver_transmettre_ouverture_sans_cfvu' => 'onReserveCentral',
        ];
    }

    public function onRefuseCentral(Event $event): void
    {
        $data = $this->getDataFromEvent($event);
        if ($data === null) {
            return;
        }

        $context = $event->getContext();
        $titre = $this->hasParcours ?
            'Votre parcours ' . $this->parcours->getLibelle().' de la formation '.$this->formation->getDisplay(). ' a été refusé par le SES' :
            'Votre formation ' . $this->formation->getDisplay(). ' a été refusée par le SES';

        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/formation/refuse_central.html.twig',
            array_merge($this->getData(), ['motif' => $context['motif']])
        );
        $this->myMailer->sendMessage(
            $this->getDestinataires(true),
            '[ORéOF]  '.$titre,
            ['replyTo' => 'oreof-vp@univ-reims.fr', 'cc' => Mailer::MAIL_GENERIC]
        );
    }

    public function onValideCentral(Event $event): void
    {
        $data = $this->getDataFromEvent($event);
        if ($data === null) {
            return;
        }

        $titre = $this->hasParcours ?
            'Votre parcours ' . $this->parcours->getLibelle().' de la formation '.$this->formation->getDisplay(). ' a été validé par le SES' :
            'Votre formation ' . $this->formation->getDisplay(). ' a été validée par le SES';

        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/formation/valide_central.html.twig',
            $this->getData()
        );

        $this->myMailer->sendMessage(
            $this->getDestinataires(true),
            '[ORéOF]  '.$titre,
        );
    }

    public function onValideCentralSansCfvu(Event $event): void
    {
        $data = $this->getDataFromEvent($event);
        if ($data === null) {
            return;
        }

        $titre = $this->hasParcours ?
            'Votre parcours ' . $this->parcours->getLibelle().' de la formation '.$this->formation->getDisplay(). ' a été validé par le SES et va être publié' :
            'Votre formation ' . $this->formation->getDisplay(). ' a été validée par le SES et va être publiée';

        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/formation/valide_central_sans_cfvu.html.twig',
            $this->getData()
        );

        $this->myMailer->sendMessage(
            $this->getDestinataires(true),
            '[ORéOF]  '.$titre,
        );
    }

    public function onReserveCentral(Event $event): void
    {
        //mail au RF
        $data = $this->getDataFromEvent($event);
        if ($data === null) {
            return;
        }
        $context = $event->getContext();

        $titre = $this->hasParcours ?
            'Votre parcours ' . $this->parcours->getLibelle().' de la formation '.$this->formation->getDisplay(). ' a reçu des réserves par le SES' :
            'Votre formation ' . $this->formation->getDisplay(). ' a reçue des réserves par le SES';

        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/formation/reserve_central.html.twig',
            array_merge($this->getData(), ['motif' => $context['motif']])
        );
        $this->myMailer->sendMessage(
            $this->getDestinataires(true),
            '[ORéOF]  '.$titre,
            ['replyTo' => 'oreof-vp@univ-reims.fr', 'cc' => Mailer::MAIL_GENERIC]
        );
    }
}
