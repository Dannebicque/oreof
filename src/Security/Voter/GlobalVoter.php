<?php

namespace App\Security\Voter;

use App\Entity\Composante;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\User;
use App\Entity\UserCentre;
use App\Enums\PermissionEnum;
use App\Enums\PorteeEnum;
use App\Enums\RoleNiveauEnum;
use App\Repository\RoleRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class GlobalVoter extends Voter
{
    private string $roleNiveau;
    private string $permission;
    private string $portee;
    private User $user;

    public function __construct(
        private readonly Security       $security,
        private readonly RoleRepository $roleRepository,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
//        if (str_starts_with($attribute, 'ROLE_')) {
//            return true;
//        }

        // Attribute = CAN_{roleNiveau}_{Permission}_{portee}
        if (str_starts_with($attribute, 'CAN_')) {
            if ($this->decomposeAttribute($attribute) === false) {
                return false;
            }

            if (!in_array($this->roleNiveau, RoleNiveauEnum::getAvailableTypes())) {
                return false;
            }

            if (!in_array($this->permission, PermissionEnum::getAvailableTypes())) {
                return false;
            }

            if (!in_array($this->portee, PorteeEnum::getAvailableTypes())) {
                return false;
            }

            return true;
        }
        return false;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $this->user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$this->user instanceof UserInterface) {
            return false;
        }
        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_SES')) {
            return true;
        }

        // récupérer les centres de l'utilisateur
        // vérifier si un rôle compatible est présent sur au moins un centre de l'utilisateur
        // si non, accès interdit.
        // si oui, vérifier la portée du rôle, si besoin vérifier par hierarchie

        $roles = $this->roleRepository->findByPermission($this->getRoleFromAttribute()); // on récupère les rôles qui ont la permission demandée

        foreach ($this->user->getUserCentres() as $centre) {
            if (!array_intersect($centre->getDroits(), $roles)) {
                return false; //aucun centre en commun
            }

            // on a au moins un centre en commun, on vérifie la portée
            if ($this->portee === PorteeEnum::ALL) {
                return true; //pas de portée, c'est OK
            }

            // on a une portée, on vérifie si le centre est dans la portée
            if ($this->portee === PorteeEnum::MY->value) {
                // Soit formation ou composante, on vérifie si le centre est dans la portée
                // soit par héritage

                if ($subject instanceof Formation) {
                    if ($this->canAccessFormation($subject, $centre)) {
                        //soit centre = formation et responsable ou coresponsable,
                        //soit on remonte à la composante, et centre  = composante de la formaiton
                        return true;
                    }
                }

                if ($subject instanceof Parcours) {
                    if ($this->canAccessParcours($subject, $centre)) {
                        //soit centre = formation et responsable ou coresponsable du parcours ou de la formation,
                        //soit on remonte à la composante, et centre  = composante de la formation
                        return true;
                    }
                }

                if ($subject instanceof Composante) {
                    if ($this->canAccessComposante($subject, $centre)) {
                        //soit centre = composante et responsable ou dpe de la composante
                        return true;
                    }
                }

                if ($subject instanceof User) {
                    if ($this->canUserAccessComposante($subject, $centre)) {
                        //soit centre = composante et responsable ou dpe de la composante
                        return true;
                    }
                }
            }
        }


        return false;//pas défaut c'est non...
    }

    private function decomposeAttribute(string $attribute): bool
    {
        // die();
        $tab = explode('_', $attribute);
        if (count($tab) !== 4) {
            return false;
        }

        $this->roleNiveau = $tab[1];
        $this->permission = $tab[2];
        $this->portee = $tab[3];

        return true;
    }

    private function getRoleFromAttribute(): string
    {
        return 'ROLE_' . $this->roleNiveau . '_' . $this->permission . '_' . $this->portee;
    }

    private function canAccessFormation(Formation $subject, mixed $centre): bool
    {
        return $centre->getFormation() === $subject || $centre->getComposante() === $subject->getComposantePorteuse();
    }

    private function canAccessParcours(Parcours $subject, mixed $centre): bool
    {
        return true;
    }

    private function canAccessComposante(Composante $subject, mixed $centre): bool
    {
        return true;
    }

    private function canUserAccessComposante(User $subject, UserCentre $centre)
    {
        if (
            $this->roleNiveau === RoleNiveauEnum::COMPOSANTE->value && //todo: ou un niveau supérieur
            $this->user === $subject && $centre->getComposante() !== null
        ) {
            return true;
        }

        return false;
    }
}
