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
use App\Events\NotifCentreComposanteEvent;
use App\Repository\ComposanteRepository;
use App\Repository\UserCentreRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotifCentreComposanteSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected Mailer $mailer,
        protected ComposanteRepository $composanteRepository,
        protected UserCentreRepository $userCentreRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NotifCentreComposanteEvent::NOTIF_ADD_CENTRE_COMPOSANTE => 'onAddCentreComposante',
            NotifCentreComposanteEvent::NOTIF_REMOVE_CENTRE_COMPOSANTE => 'onRemoveCentreComposante',
        ];
    }

    public function onAddCentreComposante(NotifCentreComposanteEvent $event): void
    {
        $user = $event->user;
        $composante = $event->composante;

        if (($user === null) || ($composante === null)) {
            return;
        }

        $this->mailer->initEmail();
        $this->mailer->setTemplate(
            'mails/composante/add_centre_composante.txt.twig',
            ['user' => $user, 'composante' => $composante]
        );
        $this->mailer->sendMessage([$user->getEmail()], '[ORéOF] Accès à l\'application');
    }

    public function onRemoveCentreComposante(NotifCentreComposanteEvent $event)
    {
        $user = $event->user;
        $composante = $event->composante;

        if (($user === null) || ($composante === null)) {
            return;
        }

        $this->mailer->initEmail();
        $this->mailer->setTemplate(
            'mails/composante/remove_centre_composante.txt.twig',
            ['user' => $user, 'composante' => $composante]
        );
        $this->mailer->sendMessage([$user->getEmail()], '[ORéOF] Accès à l\'application');
    }
}
