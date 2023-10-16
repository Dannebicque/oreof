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
use App\Entity\Parcours;
use App\Repository\ComposanteRepository;
use App\Repository\FormationRepository;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowParcoursMailSubscriber implements EventSubscriberInterface
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
            'workflow.parcours.transition.valider_parcours' => 'onValideParcours',
            'workflow.parcours.transition.valider_rf' => 'onValideRf',
            'workflow.parcours.transition.reserver_rf' => 'onReserveRf',

        ];
    }

    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function onValideParcours(Event $event): void
    {
        /** @var Parcours $parcours */
        $parcours = $event->getSubject();
        $formation = $parcours->getFormation();

        if ($formation === null) {
            return;
        }
        //todo: check si le responsable de formation accepte le mail
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/parcours/valide_parcours.html.twig',
            ['parcours' => $parcours, 'formation' => $formation]
        );
        $this->myMailer->sendMessage(
            [$formation->getResponsableMention()?->getEmail(), $formation->getCoResponsable()?->getEmail()],
            '[ORéOF]  Un parcours de votre formation a été soumis à validation'
        );
    }

    public function onValideRf(Event $event)
    {
        /** @var Parcours $parcours */
        $parcours = $event->getSubject();
        $formation = $parcours->getFormation();

        if ($formation === null) {
            return;
        }
        //todo: check si le responsable de formation accepte le mail
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/parcours/valide_rf.html.twig',
            ['parcours' => $parcours, 'formation' => $formation]
        );
        $this->myMailer->sendMessage(
            [$parcours->getRespParcours()?->getEmail(), $parcours->getCoResponsable()?->getEmail()],
            '[ORéOF]  Votre parcours a été validé par le responsable de formation'
        );
    }

    public function onReserveRf(Event $event)
    {
        /** @var Parcours $parcours */
        $parcours = $event->getSubject();
        $formation = $parcours->getFormation();
        $context = $event->getContext();

        if ($formation === null) {
            return;
        }
        //todo: check si le responsable de formation accepte le mail
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/parcours/reserve_rf.html.twig',
            ['parcours' => $parcours, 'formation' => $formation, 'motif' => $context['motif']]
        );
        $this->myMailer->sendMessage(
            [$parcours->getRespParcours()?->getEmail(), $parcours->getCoResponsable()?->getEmail()],
            '[ORéOF]  Votre parcours a reçu des réserves de la part du responsable de formation'
        );
    }
}
