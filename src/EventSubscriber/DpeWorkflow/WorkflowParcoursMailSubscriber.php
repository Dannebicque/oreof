<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/EventSubscriber/DpeWorkflow/WorkflowParcoursMailSubscriber.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/10/2023 10:52
 */

namespace App\EventSubscriber\DpeWorkflow;

use App\Classes\Mailer;
use App\Repository\ComposanteRepository;
use App\Repository\FormationRepository;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Workflow\Event\Event;

/** @deprecated */
class WorkflowParcoursMailSubscriber extends AbstractDpeMailSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected UserRepository       $userRepository,
        protected ComposanteRepository $composanteRepository,
        protected FormationRepository  $formationRepository,
        protected Mailer               $myMailer
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
//            'workflow.dpeParcours.transition.valider_parcours' => 'onValideParcours',
//            'workflow.dpeParcours.transition.valider_rf' => 'onValideRf',
//            'workflow.dpeParcours.transition.reserver_rf' => 'onReserveRf',

        ];
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function onValideParcours(Event $event): void
    {
        $data = $this->getDataFromEvent($event);
        if ($data === null) {
            return;
        }

        //todo: check si le responsable de formation accepte le mail
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/parcours/valide_parcours.html.twig',
             $this->getData()
        );
        $this->myMailer->sendMessage(
            [$this->formation->getResponsableMention()?->getEmail(), $this->formation->getCoResponsable()?->getEmail()],
            '[ORéOF]  Un parcours de votre formation a été soumis à validation'
        );
    }

    public function onValideRf(Event $event): void
    {
        $data = $this->getDataFromEvent($event);
        if ($data === null) {
            return;
        }

        //todo: check si le responsable de formation accepte le mail
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/parcours/valide_rf.html.twig',
            $this->getData()
        );
        $this->myMailer->sendMessage(
            [$this->parcours->getRespParcours()?->getEmail(), $this->parcours->getCoResponsable()?->getEmail()],
            '[ORéOF]  Votre parcours a été validé par le responsable de formation'
        );
    }

    public function onReserveRf(Event $event): void
    {
        $data = $this->getDataFromEvent($event);
        if ($data === null) {
            return;
        }
        $context = $event->getContext();

        //todo: check si le responsable de formation accepte le mail
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/parcours/reserve_rf.html.twig',
            array_merge($this->getData(),
            ['motif' => $context['motif']])
        );
        $this->myMailer->sendMessage(
            [$this->parcours->getRespParcours()?->getEmail(), $this->parcours->getCoResponsable()?->getEmail()],
            '[ORéOF]  Votre parcours a reçu des réserves de la part du responsable de formation'
        );
    }
}
