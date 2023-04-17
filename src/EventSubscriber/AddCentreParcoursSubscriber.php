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
use App\Entity\Parcours;
use App\Entity\User;
use App\Entity\UserCentre;
use App\Enums\CentreGestionEnum;
use App\Events\AddCentreParcoursEvent;
use App\Events\UserEvent;
use App\Events\UserRegisterEvent;
use App\Repository\ComposanteRepository;
use App\Repository\FormationRepository;
use App\Repository\ParcoursRepository;
use App\Repository\UserCentreRepository;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AddCentreParcoursSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected Mailer $mailer,
        protected ParcoursRepository $parcoursRepository,
        protected UserCentreRepository $userCentreRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AddCentreParcoursEvent::ADD_CENTRE_PARCOURS => 'onAddCentreParcours',
            AddCentreParcoursEvent::REMOVE_CENTRE_PARCOURS => 'onRemoveCentreParcours',
        ];
    }

    public function onAddCentreParcours(AddCentreParcoursEvent $event): void
    {
        $user = $event->user ?? $event->parcours->getRespParcours();
        $parcours = $event->parcours;

        if (($user === null) || ($parcours === null)) {
            return;
        }

        // on vérifie s'il est déjà dans un centre
        $existe = $this->userCentreRepository->findOneBy(['user' => $user, 'formation' => $parcours->getFormation()]);
        // si oui, on vérifie s'il est déjà dans la formation
        if ($existe !== null) {
            //on vérifie s'il a les droits suffisants
            if (!in_array($event->droits[0], $existe->getDroits(), true)) {
                // on ne fait rien
                $existe->addRoleCode($event->droits[0]);
                $this->userCentreRepository->save($existe, true);
            }
        } else {
            // on ajoute le centre à la formation
            $centre = new UserCentre();
            $centre->setUser($user);
            $centre->setFormation($parcours->getFormation());
            $centre->setDroits($event->droits);
            $this->userCentreRepository->save($centre, true);
        }

        $this->mailer->initEmail();
        $this->mailer->setTemplate(
            'mails/parcours/add_centre_parcours.txt.twig',
            ['user' => $user, 'parcours' => $parcours]
        );
        $this->mailer->sendMessage([$user->getEmail()], '[ORéOF] Accès à l\'application');
    }

    public function onRemoveCentreParcours(AddCentreParcoursEvent $event)
    {
        $user = $event->user !== null ? $event->user : $event->parcours->getRespParcours();
        $parcours = $event->parcours;

        if (($user === null) || ($parcours === null)) {
            return;
        }

        //on vérifie s'il est déjà dans le centre
        $existe = $this->userCentreRepository->findOneBy(['user' => $user, 'formation' => $parcours->getFormation()]);

        //si oui, on vérifie s'il n'est pas responsable sur un autre parcours de la formation. Si non on supprimer le centre
        if ($existe !== null) {
            $parcour = $this->parcoursRepository->findRespOtherParcoursInFormation($parcours, $user);
            if (count($parcour) === 0) {
                $this->userCentreRepository->remove($existe, true);
            }
        }

        $this->mailer->initEmail();
        $this->mailer->setTemplate(
            'mails/parcours/remove_centre_parcours.txt.twig',
            ['user' => $user, 'parcours' => $parcours]
        );
        $this->mailer->sendMessage([$user->getEmail()], '[ORéOF] Accès à l\'application');
    }

    private function sendMail(User $user, Parcours $parcours)
    {

    }
}
