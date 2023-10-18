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

class DpePublicationMailSubscriber implements EventSubscriberInterface
{
    public const EMAIL_CENTRAL = 'cfvu-secretariat@univ-reims.fr'; //todo: a mettre sur établissement ?

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
            'workflow.dpe.transition.valider_publication' => 'onValidePublication',
            'workflow.dpe.transition.publier' => 'onPublie',
        ];
    }



    public function onValideCfvu(Event $event)
    {
        //mail aux DPE + RF ?
    }

    public function onValidePublication(Event $event)
    {
        //mail aux DPE + RF ?
        //tache cron qui scrute la date, publi les éléments et change le marquage de la formation + mail sur event onPublie
    }
}
