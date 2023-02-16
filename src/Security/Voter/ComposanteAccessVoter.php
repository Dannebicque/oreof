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

class ComposanteAccessVoter extends Voter
{

    public const ROLE_COMPOSANTE_EDIT_MY = 'ROLE_COMPOSANTE_EDIT_MY';
    public const ROLE_COMPOSANTE_SHOW_MY = 'ROLE_COMPOSANTE_SHOW_MY';

    private array $roles;

    public function __construct(
        private Security $security,
        private RoleRepository $roleRepository,
    )
    {
    }

    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, Composante::class, true);
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::ROLE_COMPOSANTE_EDIT_MY, self::ROLE_COMPOSANTE_SHOW_MY], true)
            && $subject instanceof Composante;
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

        switch ($attribute) {
            case self::ROLE_COMPOSANTE_SHOW_MY:
                return $this->hasShowOnHisComposante($user, $subject);
            case self::ROLE_COMPOSANTE_EDIT_MY:
                return $this->hasEditOnHisComposante($user, $subject);

        }

        return false;
    }

    private function hasShowOnHisComposante(UserInterface $user, Composante $subject): bool
    {
        /** @var User $user */
        foreach ($user->getUserCentres() as $centre) {
            if ($centre->getComposante() === $subject && count(array_intersect($centre->getDroits(), $this->roles)) > 0 ) {
                return true;
            }
        }

        return false;
    }

    private function hasEditOnHisComposante(UserInterface $user, Composante $subject): bool
    {
    }


}
