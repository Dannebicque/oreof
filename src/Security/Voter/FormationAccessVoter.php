<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Security/Voter/FormationAccessVoter.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Security\Voter;

use App\Entity\Formation;
use App\Entity\User;
use App\Repository\RoleRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class FormationAccessVoter extends Voter
{
    public const ROLE_FORMATION_EDIT_MY = 'ROLE_FORMATION_EDIT_MY';
    public const ROLE_FORMATION_SHOW_MY = 'ROLE_FORMATION_SHOW_MY';

    private array $roles;

    public function __construct(
        private readonly Security $security,
        private readonly RoleRepository $roleRepository,
    ) {
    }

    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, Formation::class, true);
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::ROLE_FORMATION_EDIT_MY, self::ROLE_FORMATION_SHOW_MY], true)
            && $subject instanceof Formation;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_SES')) {
            return true;
        }

        $this->roles = $this->roleRepository->findByPermission($attribute);

        return match ($attribute) {
            self::ROLE_FORMATION_SHOW_MY => $this->hasShowOnHisFormation($user, $subject),
            self::ROLE_FORMATION_EDIT_MY => $this->hasEditOnHisFormation($user, $subject),
            default => false,
        };
    }

    private function hasShowOnHisFormation(UserInterface|User $user, Formation $subject): bool
    {
        /** @var User $user */
        foreach ($user->getUserCentres() as $centre) {
            if (($centre->getFormation() === $subject || $centre->getComposante() === $subject->getComposantePorteuse()) && count(array_intersect($centre->getDroits(), $this->roles)) > 0) {
                return true;
            }
        }

        return false;
    }

    private function hasEditOnHisFormation(UserInterface|User $user, Formation $subject): bool
    {
        foreach ($user->getUserCentres() as $centre) {
            if (($centre->getFormation() === $subject || $centre->getComposante() === $subject->getComposantePorteuse()) &&
                count(array_intersect($centre->getDroits(), $this->roles)) > 0) {
                return true;
            }
        }

        return false;
    }
}
