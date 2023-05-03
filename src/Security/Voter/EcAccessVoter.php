<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Security/Voter/EcAccessVoter.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Security\Voter;

use App\Entity\FicheMatiere;
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
    ) {
    }

    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, FicheMatiere::class, true);
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::ROLE_EC_EDIT_MY], true)
            && $subject instanceof FicheMatiere;
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
            self::ROLE_EC_EDIT_MY => $this->isReponsableFiche($user, $subject),
            default => false,
        };
    }

    private function isReponsableFiche(UserInterface|User $user, FicheMatiere $subject): bool
    {
        //todo: responsable fiche, parcours (si parcours d'origine), formation (si parcours d'origine de la fiche)
        return $subject->getResponsableFicheMatiere()?->getId() === $user->getId();
    }
}
