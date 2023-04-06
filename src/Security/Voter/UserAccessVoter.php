<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Security/Voter/EcAccessVoter.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Security\Voter;

use App\Entity\ElementConstitutif;
use App\Entity\Formation;
use App\Entity\User;
use App\Repository\RoleRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserAccessVoter extends Voter
{
    public const CAN_EDIT_CENTRE = 'CAN_EDIT_CENTRE';

    private array $roles;

    public function __construct(
        private readonly Security $security,
        private readonly RoleRepository $roleRepository,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CAN_EDIT_CENTRE], true);
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
            self::CAN_EDIT_CENTRE => $this->canEditCentre($user, $subject),
            default => false,
        };
    }

    private function canEditCentre(UserInterface|User $user, mixed $subject): bool
    {
        //todo: tester si subjet est composante et si je suis DPE avec les droits
        return $subject->getResponsableEc()?->getId() === $user->getId();
    }
}
