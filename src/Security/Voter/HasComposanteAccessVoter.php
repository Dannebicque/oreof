<?php

namespace App\Security\Voter;

use App\Entity\Composante;
use App\Entity\User;
use App\Repository\RoleRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\CacheableVoterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class HasComposanteAccessVoter extends Voter
{
    public const ROLE_COMPOSANTE = 'ROLE_COMPOSANTE';
    public const ROLE_COMPOSANTE_SHOW_ALL = 'ROLE_COMPOSANTE_SHOW_ALL';
    public const ROLE_FORMATION_ADD_ALL = 'ROLE_FORMATION_ADD_ALL';

    public const ROLE_FORMATION = 'ROLE_FORMATION';

    private array $roles;

    public function __construct(
        private readonly Security $security,
        private readonly RoleRepository $roleRepository,
    )
    {
    }

    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, User::class, true);
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute ,[self::ROLE_COMPOSANTE, self::ROLE_FORMATION, self::ROLE_FORMATION_ADD_ALL], true)
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $this->roles = $this->roleRepository->findByPermission($attribute);


        return match ($attribute) {
            self::ROLE_COMPOSANTE => $this->isCentreComposante($user),
            self::ROLE_FORMATION => $this->isCentreFormation($user),
            self::ROLE_FORMATION_ADD_ALL => $this->isCentreFormation($user),
            self::ROLE_COMPOSANTE_SHOW_ALL => $this->hasShowOnAllComposante($user),
            default => false,
        };

    }

    private function isCentreComposante(UserInterface|User $user): bool
    {
        if ($user->getComposanteResponsableDpe()->count() > 0) {
            return true;
        }
        /** @var User $user */
        foreach ($user->getUserCentres() as $centre) {
            if ($centre->getComposante() === null && $centre->getFormation() === null) {
                return true; //donc centre etablissement
            }
            if ($centre->getComposante() !== null) {
                return true; //au moins une composante
            }
        }

        return false;
    }

    private function isCentreFormation(UserInterface|User $user): bool
    {
        if ($user->getFormationsResponsableMention()->count() > 0) {
            return true;
        }

        /** @var User $user */
        foreach ($user->getUserCentres() as $centre) {
            if ($centre->getComposante() === null && $centre->getFormation() === null) {
                return true; //donc centre etablissement
            }
            if ($centre->getFormation() !== null) {
                return true; //au moins une formation
            }
        }

        return false;
    }

    private function hasShowOnAllComposante(UserInterface|User $user): bool
    {
        return count(array_intersect($this->roles, $user->getRoles())) > 0;
    }
}
