<?php
/*
 * Copyright (c) 2025. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Security/Voter/RessourceVoter.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 26/05/2025 16:32
 */

// src/Security/Voter/ResourceVoter.php
namespace App\Security\Voter;

use App\Classes\GetDpeParcours;
use App\Entity\Composante;
use App\Entity\DpeParcours;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\Etablissement;
use App\Entity\User;
use App\Entity\UserProfil;
use App\Enums\CentreGestionEnum;
use App\Enums\PermissionEnum;
use App\Enums\TypeModificationDpeEnum;
use App\Repository\UserProfilRepository;
use App\Repository\ProfilDroitsRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RessourceVoter extends Voter
{
    public function __construct(
        private UserProfilRepository   $userProfilRepository,
        private ProfilDroitsRepository $profilDroitsRepository,
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, PermissionEnum::getAvailableTypes())
            && is_array($subject)
            && isset($subject['route'], $subject['subject']);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $route = $subject['route'];
        $object = $subject['subject'];

        // Récupère tous les profils liés à l'utilisateur
        $userProfils = $this->userProfilRepository->findBy(['user' => $user]);

        foreach ($userProfils as $userProfil) {
            $profile = $userProfil->getProfil();
            dump($attribute);
            // Vérifie si ce profil a explicitement le droit demandé sur la route précise
            if ($this->profilDroitsRepository->hasDroit($profile, $attribute, $route)) {
                // Vérifie la portée
                if ($this->checkScope($userProfil, $object, $attribute)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function checkScope(UserProfil $userProfil, mixed $object, string $attribute): bool
    {
        $centre = $userProfil->getProfil()?->getCentre();

        switch ($centre) {
            case CentreGestionEnum::CENTRE_GESTION_ETABLISSEMENT:
                return $this->checkEtablissement($userProfil, $object, $attribute) || $object === 'etablissement';
            case CentreGestionEnum::CENTRE_GESTION_COMPOSANTE:
                return $this->checkComposante($userProfil, $object, $attribute) || $object === 'composante';
            case CentreGestionEnum::CENTRE_GESTION_FORMATION:
                return $this->checkFormation($userProfil, $object, $attribute) || $object === 'formation';
            case CentreGestionEnum::CENTRE_GESTION_PARCOURS:
                return $this->checkParcours($userProfil, $object, $attribute) || $object === 'parcours';
        }

        return false;
    }

    private function checkEtablissement(UserProfil $userProfil, mixed $object, string $attribute): bool
    {
        if ($object instanceof Etablissement) {
            return $userProfil->getEtablissement() === $object;
        }

        return false;
    }

    private function checkComposante(UserProfil $userProfil, mixed $object, string $attribute): bool
    {
        if ($object instanceof Composante) {
            return $userProfil->getComposante() === $object;
        }

        return false;
    }

    private function checkFormation(UserProfil $userProfil, mixed $object, string $attribute): bool
    {
        if ($object instanceof Formation) {
            $isProprietaire = $userProfil->getFormation() === $object;

            $canAccess =
                $object->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_TEXTE ||
                $object->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_MCCC_TEXTE ||
                $object->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_MCCC ||
                $object->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_PARCOURS ||
                $object->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION;

            if ($object->isHasParcours() === false) {
                // parcours par défaut
                $parcours = $object->getParcours()->first();
                if ($parcours !== null && $parcours instanceof Parcours) {
                    $dpeParcours = GetDpeParcours::getFromParcours($parcours);
                    if ($dpeParcours !== null) {
                        $canAccess = $canAccess || $this->checkParcours($dpeParcours, $centre);
                    }
                }
            }

            return $canAccess && $isProprietaire;
        }

        return false;
    }

    private function checkParcours(UserProfil $userProfil, mixed $object, string $attribute): bool
    {
        if ($object instanceof Parcours) {
            return $userProfil->getParcours() === $object;
        }

        return false;
    }
}
