<?php

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
        protected UserRepository $userRepository,
        protected ComposanteRepository $composanteRepository,
        protected FormationRepository $formationRepository,
        protected Mailer $myMailer
    ) {
    }

    public function onUserDemandeAcces(UserRegisterEvent $event): void
    {
        $admins = $this->userRepository->findByRole('ROLE_ADMIN');
        $user = $event->getUser();
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate('mails/user/confirm_demande.txt.twig',
            ['user' => $user]);
        $this->myMailer->sendMessage([$user->getEmail()], '[ORéOF] Confirmation demande accès');

        $valid = null;

        //si centre : Composante ou Formation
        if ($event->getCentre() === CentreGestionEnum::CENTRE_GESTION_COMPOSANTE) {
            $comp = $event->getComposante();
            if ($comp !== null && $comp->getResponsableDpe() !== null) {
                $valid = $comp->getResponsableDpe();
            }
        } elseif ($event->getCentre() === CentreGestionEnum::CENTRE_GESTION_FORMATION) {
            $formation = $event->getFormation();
            if ($formation !== null && $formation->getResponsableMention() !== null) {
                $valid = $formation->getResponsableMention();
            }
        }

        if ($valid !== null) {
            $this->myMailer->initEmail();
            $this->myMailer->setTemplate('mails/user/demande_en_attente_dpe.txt.twig',
                ['user' => $user, 'dpe' => $valid]);
            $this->myMailer->sendMessage([$valid->getEmail()],
                '[ORéOF]  Une demande d\'accès est en attente de validation');
        }

        //si centre : Etablissement
        foreach ($admins as $admin) {
            $this->myMailer->initEmail();
            $this->myMailer->setTemplate('mails/user/demande_en_attente_admin.txt.twig',
                ['user' => $user, 'admin' => $admin]);
            $this->myMailer->sendMessage([$admin->getEmail()],
                '[ORéOF] Une demande d\'accès est en attente de validation');
        }
    }

    public function onUserRevoqueAdmin()
    {
    }

    public function onUserValideAdmin(UserEvent $event): void
    {
        $user = $event->getUser();
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate('mails/user/acces_valide.txt.twig',
            ['user' => $user]);
        $this->myMailer->sendMessage([$user->getEmail()], '[ORéOF] Validation demande accès');
    }

    public function onUserValideDpe(UserEvent $event): void
    {
        $admins = $this->userRepository->findByRole('ROLE_ADMIN');

        $user = $event->getUser();

        foreach ($admins as $admin) {
            $this->myMailer->initEmail();
            $this->myMailer->setTemplate('mails/user/acces_valide_dpe.txt.twig',
                ['user' => $user, 'admin' => $admin]);
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
            UserEvent::USER_AJOUTE => 'onUserAjoute',
        ];
    }

    public function onUserAjoute(UserEvent $event)
    {
        $user = $event->getUser();
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate('mails/user/acces_ajoute.txt.twig',
            ['user' => $user]);
        $this->myMailer->sendMessage([$user->getEmail()], '[ORéOF] Accès à l\'application');
    }
}
