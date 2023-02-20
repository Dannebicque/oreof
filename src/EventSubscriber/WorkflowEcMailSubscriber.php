<?php

namespace App\EventSubscriber;

use App\Classes\Mailer;
use App\Entity\Formation;
use App\Repository\ComposanteRepository;
use App\Repository\FormationRepository;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowEcMailSubscriber implements EventSubscriberInterface
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
            'workflow.ec.transition.initialiser' => 'onInitialise',

        ];
    }

    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function onInitialise(Event $event): void
    {
        /** @var \App\Entity\ElementConstitutif $ec */
        $ec = $event->getSubject();
        if ($ec->getResponsableEc() !== null) {
            //todo: check si le responsable de EC accepte le mail
            $this->myMailer->initEmail();
            $this->myMailer->setTemplate('mails/ec/ouverture_redaction_ec.txt.twig',
                ['ec' => $ec, 'responsable' => $ec->getResponsableEc(), 'formation' => $ec->getParcours()->getFormation()]);
            $this->myMailer->sendMessage([$ec->getResponsableEc()?->getEmail()],
                '[ORéOF]  Un élément constitutif est ouvert pour la rédaction/modification');
        }
    }

}
