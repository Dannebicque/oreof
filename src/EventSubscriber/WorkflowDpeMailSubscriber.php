<?php

namespace App\EventSubscriber;

use App\Classes\Mailer;
use App\Entity\Formation;
use App\Repository\ComposanteRepository;
use App\Repository\FormationRepository;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowDpeMailSubscriber implements EventSubscriberInterface
{

    public function __construct(
        protected UserRepository $userRepository,
        protected ComposanteRepository $composanteRepository,
        protected FormationRepository $formationRepository,
        protected Mailer $myMailer
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.dpe.transition.initialiser' => 'onInitialise',

        ];
    }

    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function onInitialise(Event $event): void
    {
        /** @var Formation $formation */
        $formation = $event->getSubject();
        //todo: check si le responsable de formation accepte le mail
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate('mails/dpe/ouverture_redaction_formation.txt.twig',
            ['formation' => $formation, 'responsable' => $formation->getResponsableMention()]);
        $this->myMailer->sendMessage([$formation->getResponsableMention()?->getEmail()],
            '[ORéOF]  Une formation est ouverture pour la rédaction/modification');
    }

}
