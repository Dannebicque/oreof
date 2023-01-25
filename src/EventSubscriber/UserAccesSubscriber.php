<?php

namespace App\EventSubscriber;

use App\Classes\Mailer;
use App\Events\UserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserAccesSubscriber implements EventSubscriberInterface
{
    public function __construct(protected Mailer $myMailer)
    {
    }

    public function onUserDemandeAcces(UserEvent $event): void
    {
        $user = $event->getUser();
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate('mails/user/confirm_demande.txt.twig',
            ['user' => $user]);
        $this->myMailer->sendMessage([$user->getEmail()], '[ORéOF] Confirmation demande accès');

        //si centre : Composante ou Formation
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate('mails/user/demande_en_attente_dpe.txt.twig',
            ['user' => $user]);
        $this->myMailer->sendMessage([$dep->getEmail()], '[ORéOF]  Une demande d\'accès est en attente de validation');

        //si centre : Etablissement
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate('mails/user/demande_en_attente_admin.txt.twig',
            ['user' => $user]);
        $this->myMailer->sendMessage([$admin->getEmail()], '[ORéOF] Une demande d\'accès est en attente de validation');
    }

    public function onUserValideAdmin(UserEvent $event)
    {
        $user = $event->getUser();
        $this->myMailer->initEmail();
        $this->myMailer->setTemplate('mails/user/acces_valide.txt.twig',
            ['user' => $user]);
        //todo: copie DPE
        $this->myMailer->sendMessage([$user->getEmail()], '[ORéOF] Validation demande accès');
    }

    public function onUserValideDpe(UserEvent $event)
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
        ];
    }
}
