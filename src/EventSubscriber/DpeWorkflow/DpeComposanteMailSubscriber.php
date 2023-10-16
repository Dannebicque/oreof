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

class DpeComposanteMailSubscriber extends AbstractDpeMailSubscriber implements EventSubscriberInterface
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
            'workflow.dpe.transition.valide_rf' => 'onValideRf',
            'workflow.dpe.transition.refuser_dpe_composante' => 'onRefuseDpeComposante',
            'workflow.dpe.transition.reserver_dpe_composante' => 'onReserveDpeComposante',
            'workflow.dpe.transition.refuser_conseil' => 'onRefuseConseil',
            'workflow.dpe.transition.valider_conseil' => 'onValideConseil',
            'workflow.dpe.transition.reserver_conseil' => 'onReserveConseil',
        ];
    }

    public function onRefuseConseil(Event $event): void
    {
        //mail au RF
        /** @var Formation $formation */
        $formation = $event->getSubject();
        $context = $event->getContext();

        //todo: check si le responsable de formation accepte le mail
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/formation/refuse_conseil_composante.html.twig',
            ['formation' => $formation, 'motif' => $context['motif']]
        );
        $this->myMailer->sendMessage(
            [$formation->getResponsableMention()?->getEmail(), $formation->getCoResponsable()?->getEmail()],
            '[ORéOF]  Votre formation a été refusée par le conseil de votre composante'
        );
    }

    public function onValideConseil(Event $event): void
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
                'composante' => $formation->getComposantePorteuse(),
                'historique' => $this->getHistorique->getHistoriqueFormationLastStep($formation, 'conseil')
            ]
        );

        $this->myMailer->sendMessage(
            [self::EMAIL_CENTRAL],
            '[ORéOF]  La formation ' . $formation->getDisplayLong() . ' a été validé par le conseil de la composante'
        );

        // mail aux VP
        //suspendu sur cette phase 1
//        $vps = $this->userRepository->findByRole('ROLE_VP');
//        foreach ($vps as $vp) {
//            $this->myMailer->initEmail();
//            $this->myMailer->setTemplate(
//                'mails/workflow/formation/valide_conseil_composante.html.twig',
//                [
//                    'formation' => $formation,
//                    'composante' => $formation->getComposantePorteuse()
//                ]
//            );
//            $this->myMailer->sendMessage(
//                [$vp->getEmail()],
//                '[ORéOF]  La formation ' . $formation->getDisplayLong().' a été validé par le conseil de la composante'
//            );
//        }
    }

    public function onReserveDpeComposante(Event $event): void
    {
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
            '[ORéOF]  Votre formation a reçu des réserves de la part du DPE de votre composante'
        );
    }

    public function onReserveConseil(Event $event): void
    {
        //mail au RF
        /** @var Formation $formation */
        $formation = $event->getSubject();
        $context = $event->getContext();

        //todo: check si le responsable de formation accepte le mail
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/formation/reserve_conseil_composante.html.twig',
            ['formation' => $formation, 'motif' => $context['motif']]
        );
        $this->myMailer->sendMessage(
            [$formation->getResponsableMention()?->getEmail(), $formation->getCoResponsable()?->getEmail()],
            '[ORéOF]  Votre formation a reçu des réserves du conseil de votre composante'
        );
    }

    public function onRefuseDpeComposante(Event $event): void
    {
        //mail au RF
        /** @var Formation $formation */
        $formation = $event->getSubject();
        $context = $event->getContext();

        //todo: check si le responsable de formation accepte le mail
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/workflow/formation/refuse_dpe_composante.html.twig',
            ['formation' => $formation, 'motif' => $context['motif']]
        );
        $this->myMailer->sendMessage(
            [$formation->getResponsableMention()?->getEmail(), $formation->getCoResponsable()?->getEmail()],
            '[ORéOF]  Votre formation a été refusée par le DPE de votre composante'
        );
    }

    public function onValideRf(Event $event): void
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
}
