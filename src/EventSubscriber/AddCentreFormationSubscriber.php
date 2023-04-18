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
use App\Entity\UserCentre;
use App\Events\AddCentreFormationEvent;
use App\Repository\FormationRepository;
use App\Repository\UserCentreRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddCentreFormationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected Mailer $mailer,
        protected FormationRepository $formationRepository,
        protected UserCentreRepository $userCentreRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AddCentreFormationEvent::ADD_CENTRE_FORMATION => 'onAddCentreFormation',
            AddCentreFormationEvent::REMOVE_CENTRE_FORMATION => 'onRemoveCentreFormation',
        ];
    }

    public function onAddCentreFormation(AddCentreFormationEvent $event): void
    {
        $user = $event->user;
        $formation = $event->formation;

        if (($user === null) || ($formation === null)) {
            return;
        }

        // on vérifie s'il est déjà dans un centre
        $existe = $this->userCentreRepository->findOneBy(['user' => $user, 'formation' => $formation]);
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
            $centre->setFormation($formation);
            $centre->setDroits($event->droits);
            $this->userCentreRepository->save($centre, true);
        }

        $this->mailer->initEmail();
        $this->mailer->setTemplate(
            'mails/formation/add_centre_formation.txt.twig',
            ['user' => $user, 'formation' => $formation]
        );
        $this->mailer->sendMessage([$user->getEmail()], '[ORéOF] Accès à l\'application');
    }

    public function onRemoveCentreFormation(AddCentreFormationEvent $event)
    {
        $user = $event->user;
        $formation = $event->formation;

        if (($user === null) || ($formation === null)) {
            return;
        }

        //on vérifie s'il est déjà dans le centre
        $existe = $this->userCentreRepository->findOneBy(['user' => $user, 'formation' => $formation]);

        //si oui, on vérifie s'il n'est pas responsable sur un autre parcours de la formation. Si non on supprimer le centre
        if ($existe !== null) {
//            $parcour = $this->formationRepository->findRespOtherParcoursInFormation($formation, $user);
//            if (count($formation) === 0) {/
                $this->userCentreRepository->remove($existe, true);
//            }
        }

        $this->mailer->initEmail();
        $this->mailer->setTemplate(
            'mails/formation/remove_centre_formation.txt.twig',
            ['user' => $user, 'formation' => $formation]
        );
        $this->mailer->sendMessage([$user->getEmail()], '[ORéOF] Accès à l\'application');
    }
}
