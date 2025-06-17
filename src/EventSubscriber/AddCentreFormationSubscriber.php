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
use App\Entity\UserProfil;
use App\Events\AddCentreFormationEvent;
use App\Repository\FormationRepository;
use App\Repository\ProfilRepository;
use App\Repository\UserCentreRepository;
use App\Repository\UserProfilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddCentreFormationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected Mailer               $mailer,
        protected FormationRepository  $formationRepository,
        protected UserProfilRepository          $userProfilRepository,
        private readonly ProfilRepository       $profilRepository,
        private readonly EntityManagerInterface $entityManager,
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
        $campagneCollecte = $event->campagneCollecte;

        // on vérifie s'il est déjà dans un centre
        /** @var UserProfil $existe */
        $existe = $this->userProfilRepository->findOneBy(['user' => $user, 'formation' => $formation]);
        // si oui, on vérifie s'il est déjà dans la formation

        if ($existe !== null) {
            //S'il existe on met à jour les droits
            if (!$event->droits[0] === $existe->getProfil()?->getCode()) {
                // on récupère le profil correspondant
                $profil = $this->profilRepository->findOneBy(['code' => $event->droits[0]]);
                $existe->setProfil($profil);
                $this->entityManager->flush();
            }
        } else {
            // on ajoute le centre à la formation
            $profil = $this->profilRepository->findOneBy(['code' => $event->droits[0]]);
            $centre = new UserProfil();
            $centre->setUser($user);
            $centre->setFormation($formation);
            $centre->setCampagneCollecte($campagneCollecte);
            $centre->setProfil($profil);
            $this->entityManager->persist($centre);
            $this->entityManager->flush();
        }

        $this->mailer->initEmail();
        $this->mailer->setTemplate(
            'mails/formation/add_centre_formation.txt.twig',
            ['user' => $user, 'formation' => $formation]
        );
        $this->mailer->sendMessage([$user->getEmail()], '[ORéOF] Accès à l\'application');
    }

    public function onRemoveCentreFormation(AddCentreFormationEvent $event): void
    {
        $user = $event->user;
        $formation = $event->formation;

        //on vérifie s'il est déjà dans le centre
        $existe = $this->userProfilRepository->findOneBy(['user' => $user, 'formation' => $formation]);

        //si oui, on supprime le centre
        if ($existe !== null) {
            $this->entityManager->remove($existe);
            $this->entityManager->flush();

            $this->mailer->initEmail();
            $this->mailer->setTemplate(
                'mails/formation/remove_centre_formation.txt.twig',
                ['user' => $user, 'formation' => $formation]
            );
            $this->mailer->sendMessage([$user->getEmail()], '[ORéOF] Accès à l\'application');
        }
    }
}
