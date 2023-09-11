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
use PhpOffice\PhpSpreadsheet\Calculation\TextData\Format;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowDpeMailSubscriber implements EventSubscriberInterface
{
    public const EMAIL_CENTRAL = 'cfvu@univ-reims.fr'; //todo: a mettre sur établissement ?

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
            'workflow.dpe.transition.initialiser' => 'onInitialise',
            'workflow.dpe.transition.valide_rf' => 'onValideRf',
            'workflow.dpe.transition.refuser_dpe_composante' => 'onRefuseDpeComposante',
            // 'workflow.dpe.transition.valide_dpe_composante' => 'onValideDpeComposante', // mail aux conseillers ou rien ?
            'workflow.dpe.transition.reserver_dpe_composante' => 'onReserveDpeComposante',
            'workflow.dpe.transition.refuser_conseil' => 'onRefuseConseil',
            'workflow.dpe.transition.valider_conseil' => 'onValideConseil',
            'workflow.dpe.transition.reserver_conseil' => 'onReserveConseil',
            'workflow.dpe.transition.laisser_passer_conseil' => 'onLaisserPasserConseil',
            'workflow.dpe.transition.refuser_central' => 'onRefuseCentral',
            'workflow.dpe.transition.valider_central' => 'onValideCentral',
            'workflow.dpe.transition.reserver_central' => 'onReserveCentral',
            'workflow.dpe.transition.transmettre_cfvu' => 'onTransmettreCfvu',
            'workflow.dpe.transition.refuser_definitif_cfvu' => 'onRefuseDefinitifCfvu',
            'workflow.dpe.transition.refuser_revoir_cfvu' => 'onRefuseRevoirCfvu',
            'workflow.dpe.transition.valider_reserve_cfvu' => 'onValideReserveCfvu',
            'workflow.dpe.transition.valider_reserve_conseil_cfvu' => 'onValideReserveConseilCfvu',
            'workflow.dpe.transition.valider_reserve_central_cfvu' => 'onValideReserveCentralCfvu',
            'workflow.dpe.transition.valider_cfvu' => 'onValideCfvu',
            'workflow.dpe.transition.valider_publication' => 'onValidePublication',
            'workflow.dpe.transition.publier' => 'onPublie',
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
        $this->myMailer->setTemplate(
            'mails/dpe/ouverture_redaction_formation.txt.twig',
            ['formation' => $formation, 'responsable' => $formation->getResponsableMention()]
        );
        $this->myMailer->sendMessage(
            [$formation->getResponsableMention()?->getEmail()],
            '[ORéOF]  Une formation est ouverte pour la rédaction/modification'
        );
    }
    public function onValideCentral(Event $event)
    {
    }

    public function onTransmettreCfvu(Event $event)
    {
        //mail aux conseillers ?
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

    public function onValideConseil(Event $event)
    {
        /** @var Formation $formation */
        $formation = $event->getSubject();

        if ($formation === null) {
            return;
        }
        //todo: check si le responsable de formation accepte le mail

        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/formation/valide_conseil_composante.html.twig',
            [
                'formation' => $formation,
                'composante' => $formation->getComposantePorteuse()
            ]
        );

        $this->myMailer->sendMessage(
            [self::EMAIL_CENTRAL],
            '[ORéOF]  La formation ' . $formation->getDisplayLong().' a été validé par le conseil de la composante'
        );
    }

    public function onReserveDpeComposante(Event $event){
        //mail au RF
        /** @var Formation $formation */
        $formation = $event->getSubject();
        $context = $event->getContext();

        //todo: check si le responsable de formation accepte le mail
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/formation/reserve_dpe_composante.html.twig',
            ['formation' => $formation, 'motif' => $context['motif']]
        );
        $this->myMailer->sendMessage(
            [$formation->getResponsableMention()?->getEmail(), $formation->getCoResponsable()?->getEmail()],
            '[ORéOF]  Votre formation a reçu des réserves du DPE de votre composante'
        );
    }
    public function onValideRf(Event $event)
    {
        /** @var Formation $formation */
        $formation = $event->getSubject();

        if ($formation === null) {
            return;
        }
        //todo: check si le responsable de formation accepte le mail
        $dpe = $formation->getComposantePorteuse()?->getResponsableDpe();

        if ($dpe === null) {
            return;
        }

        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/formation/valide_rf.html.twig',
            ['formation' => $formation, 'dpe' => $dpe]
        );

        $this->myMailer->sendMessage(
            [$dpe->getEmail()],
            '[ORéOF]  Une formation a été validé par son responsable'
        );
    }

    public function onValideDpeComposante(Event $event)
    {
    }
}
