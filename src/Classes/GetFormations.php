<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/GetFormations.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 14/02/2024 08:57
 */

namespace App\Classes;

use App\Entity\CampagneCollecte;
use App\Entity\User;
use App\Repository\FormationRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class GetFormations
{

    public function __construct(
        private FormationRepository $formationRepository,
        private AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    public function getFormations(
        User|UserInterface $user,
        CampagneCollecte $campagneCollecte,
        array $options = [],
        bool $isCfvu = false
    ): array {
        $sort = $options['sort'] ?? 'typeDiplome';
        $direction = $options['direction'] ?? 'asc';
        $q = $options['q'] ?? null;

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN') ||
            $this->authorizationChecker->isGranted('CAN_COMPOSANTE_SHOW_ALL', $user) ||
            $this->authorizationChecker->isGranted('CAN_ETABLISSEMENT_SHOW_ALL', $user) ||
            $this->authorizationChecker->isGranted('CAN_FORMATION_SHOW_ALL', $user)) {
            $formations = $this->formationRepository->findBySearch($q, $campagneCollecte, $options);
        } else {
            $formations = [];
            //gérer le cas ou l'utilisateur dispose des droits pour lire la composante
            $centres = $user?->getUserCentres();
            foreach ($centres as $centre) {
                //todo: gérer avec un voter
                if ($centre->getComposante() !== null && (
                    in_array('Gestionnaire', $centre->getDroits()) ||
                        in_array('Invité', $centre->getDroits()) ||
                        in_array('ROLE_SCOL', $centre->getDroits()) ||
                        in_array('ROLE_COMM', $centre->getDroits()) ||
                        in_array('Directeur', $centre->getDroits())
                )) {
                    //todo: il faudrait pouvoir filtrer par ce que contient le rôle et pas juste le nom
                    $formations[] = $this->formationRepository->findByComposante(
                        $centre->getComposante(),
                        $campagneCollecte,
                        [$sort => $direction],
                        $q
                    );
                }

                if ($centre->getFormation() !== null && (
                    in_array('ROLE_FORMATION_LECTEUR', $centre->getDroits()) || in_array('ROLE_GEST_FORM', $centre->getDroits() )
                )) {
                    $formations[] = [$centre->getFormation()];
                }
            }

            $formations[] = $this->formationRepository->findByComposanteDpe(
                $user,
                $campagneCollecte,
                [$sort => $direction]
            );
            $formations[] = $this->formationRepository->findByResponsableOuCoResponsable(
                $user,
                $campagneCollecte,
                [$sort => $direction]
            );
            $formations[] = $this->formationRepository->findByResponsableOuCoResponsableParcours(
                $user,
                $campagneCollecte,
                [$sort => $direction]
            );

            $formations = array_merge(...$formations);
        }
        $tFormations = [];
        foreach ($formations as $formation) {
            $tFormations[$formation->getId()] = $formation;
        }

        return $tFormations;
    }
}
