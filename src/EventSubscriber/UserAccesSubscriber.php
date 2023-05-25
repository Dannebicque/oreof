<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/EventSubscriber/UserAccesSubscriber.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\EventSubscriber;

use App\Classes\Mailer;
use App\Enums\CentreGestionEnum;
use App\Events\UserEvent;
use App\Events\UserRegisterEvent;
use App\Repository\ComposanteRepository;
use App\Repository\FormationRepository;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserAccesSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected UserRepository       $userRepository,
        protected ComposanteRepository $composanteRepository,
        protected FormationRepository  $formationRepository,
        protected Mailer               $myMailer
    ) {
    }

    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function onUserDemandeAcces(UserRegisterEvent $event): void
    {
        $admins = $this->userRepository->findByRole('ROLE_ADMIN');
        $user = $event->getUser();
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/user/confirm_demande.txt.twig',
            ['user' => $user]
        );
        $this->myMailer->sendMessage([$user->getEmail()], '[ORéOF] Confirmation demande accès');

        $valid = null;

        //si centre : Composante ou Formation
        if ($event->getCentre() === CentreGestionEnum::CENTRE_GESTION_COMPOSANTE) {
            $comp = $event->getComposante();
            if ($comp !== null && $comp->getResponsableDpe() !== null) {
                $valid = $comp->getResponsableDpe();
            }
        }


        if ($valid !== null) {
            $this->myMailer->initEmail();
            $this->myMailer->setTemplate(
                'mails/user/demande_en_attente_dpe.txt.twig',
                ['user' => $user, 'dpe' => $valid]
            );
            $this->myMailer->sendMessage(
                [$valid->getEmail()],
                '[ORéOF]  Une demande d\'accès est en attente de validation'
            );
        }

        //si centre : Etablissement
        foreach ($admins as $admin) {
            $this->myMailer->initEmail();
            $this->myMailer->setTemplate(
                'mails/user/demande_en_attente_admin.txt.twig',
                ['user' => $user, 'admin' => $admin]
            );
            $this->myMailer->sendMessage(
                [$admin->getEmail()],
                '[ORéOF] Une demande d\'accès est en attente de validation'
            );
        }
    }

    public function onUserRefuserAdmin(UserEvent $event)
    {
        $admins = $this->userRepository->findByRole('ROLE_ADMIN');

        $user = $event->getUser();
        if ($user->getComposanteDemande() !== null && $user->getComposanteDemande()->getResponsableDpe() !== null) {
            $this->myMailer->initEmail();
            $this->myMailer->setTemplate(
                'mails/user/acces_refuse_dpe.txt.twig',
                [
                    'dpe' => $user->getComposanteDemande()->getResponsableDpe(),
                    'user' => $user,
                    'motif' => $event->getMotif()]
            );
            $this->myMailer->sendMessage([$user->getEmail()], '[ORéOF] Refus de votre demande accès');

            foreach ($admins as $admin) {
                $this->myMailer->initEmail();
                $this->myMailer->setTemplate(
                    'mails/user/acces_refuse_admin.txt.twig',
                    [
                        'admin' => $admin,
                        'user' => $user,
                        'motif' => $event->getMotif()]
                );
                $this->myMailer->sendMessage([$admin->getEmail()], '[ORéOF] Refus d\'une demande accès');
            }
        }

        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/user/acces_refuse.txt.twig',
            ['user' => $user, 'motif' => $event->getMotif()]
        );
        $this->myMailer->sendMessage([$user->getEmail()], '[ORéOF] Refus de votre demande accès');
    }

    public function onUserRevoqueAdmin(UserEvent $event): void
    {
    }

    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function onUserValideAdmin(UserEvent $event): void
    {
        $user = $event->getUser();
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/user/acces_valide.txt.twig',
            ['user' => $user]
        );
        $this->myMailer->sendMessage([$user->getEmail()], '[ORéOF] Validation demande accès');
    }

    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function onUserValideDpe(UserEvent $event): void
    {
        $admins = $this->userRepository->findByRole('ROLE_ADMIN');

        $user = $event->getUser();

        foreach ($admins as $admin) {
            $this->myMailer->initEmail();
            $this->myMailer->setTemplate(
                'mails/user/acces_valide_dpe.txt.twig',
                ['user' => $user, 'admin' => $admin]
            );
            $this->myMailer->sendMessage([$admin->getEmail()], '[ORéOF] Validation demande accès par le centre');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserRegisterEvent::USER_DEMANDE_ACCES => 'onUserDemandeAcces',
            UserEvent::USER_VALIDE_DPE => 'onUserValideDpe',
            UserEvent::USER_VALIDE_ADMIN => 'onUserValideAdmin',
            UserEvent::USER_REVOQUE_ADMIN => 'onUserRevoqueAdmin',
            UserEvent::USER_REFUSER_ADMIN => 'onUserRefuserAdmin',
            UserEvent::USER_AJOUTE => 'onUserAjoute',
        ];
    }

    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function onUserAjoute(UserEvent $event): void
    {
        $user = $event->getUser();
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate(
            'mails/user/acces_ajoute.txt.twig',
            ['user' => $user]
        );
        $this->myMailer->sendMessage([$user->getEmail()], '[ORéOF] Accès à l\'application');
    }
}
