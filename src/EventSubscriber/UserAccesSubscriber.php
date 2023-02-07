<?php

namespace App\EventSubscriber;

use App\Classes\Mailer;
use App\Enums\CentreGestionEnum;
use App\Events\UserEvent;
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

    public function onUserDemandeAcces(UserEvent $event): void
    {
        $admins = $this->userRepository->findByRole('ROLE_ADMIN');
        $user = $event->getUser();
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate('mails/user/confirm_demande.txt.twig',
            ['user' => $user]);
        $this->myMailer->sendMessage([$user->getEmail()], '[ORéOF] Confirmation demande accès');

        $valid = null;

        //si centre : Composante ou Formation
        if ($user->getCentreDemande() === CentreGestionEnum::CENTRE_GESTION_COMPOSANTE) {
            $comp = $this->composanteRepository->find($user->getCentreId());
            if ($comp !== null && $comp->getResponsableDpe() !== null) {
                $valid = $comp->getResponsableDpe();
            }
        } elseif ($user->getCentreDemande() === CentreGestionEnum::CENTRE_GESTION_FORMATION) {
            $formation = $this->formationRepository->find($user->getCentreId());
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

    public function onUserValideAdmin(UserEvent $event): void
    {
        $user = $event->getUser();
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate('mails/user/acces_valide.txt.twig',
            ['user' => $user]);
        //todo: copie DPE
        $this->myMailer->sendMessage([$user->getEmail()], '[ORéOF] Validation demande accès');
    }

    public function onUserValideDpe(UserEvent $event): void
    {
        $user = $event->getUser();
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate('mails/user/acces_valide_dpe.txt.twig',
            ['user' => $user]);
        //mail à admin ORéOF
        $this->myMailer->sendMessage([$user->getEmail()], '[ORéOF] Validation demande accès par le centre');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvent::USER_DEMANDE_ACCES => 'onUserDemandeAcces',
            UserEvent::USER_VALIDE_DPE => 'onUserValideDpe',
            UserEvent::USER_VALIDE_ADMIN => 'onUserValideAdmin',
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
