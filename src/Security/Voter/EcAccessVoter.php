<?php

namespace App\Security\Voter;

use App\Entity\ElementConstitutif;
use App\Entity\Formation;
use App\Entity\User;
use App\Repository\RoleRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class EcAccessVoter extends Voter
{

    public const ROLE_EC_EDIT_MY = 'ROLE_EC_EDIT_MY';

    private array $roles;

    public function __construct(
        private readonly Security $security,
        private readonly RoleRepository $roleRepository,
    )
    {
    }

    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, ElementConstitutif::class, true);
    }

    protected function supports(string $attribute, mixed $subject): bool
    {

        return in_array($attribute, [self::ROLE_EC_EDIT_MY], true)
            && $subject instanceof ElementConstitutif;
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
            self::ROLE_EC_EDIT_MY => $this->isReponsableEc($user, $subject),
            default => false,
        };

    }

    private function isReponsableEc(UserInterface|User $user, ElementConstitutif $subject): bool
    {
       return $subject->getResponsableEc()?->getId() === $user->getId();
    }
}
