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
use App\Entity\Parcours;
use App\Entity\User;
use App\Repository\RoleRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ParcoursAccessVoter extends Voter
{
    public const ROLE_PARCOURS_EDIT_MY = 'ROLE_PARCOURS_EDIT_MY';
    public const ROLE_PARCOURS_SHOW_MY = 'ROLE_PARCOURS_SHOW_MY';

    private array $roles;

    public function __construct(
        private readonly Security $security,
        private readonly RoleRepository $roleRepository,
    ) {
    }

    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, Parcours::class, true);
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::ROLE_PARCOURS_EDIT_MY, self::ROLE_PARCOURS_SHOW_MY], true)
            && $subject instanceof Parcours;
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

//        if (!($this->parcoursWorkflow->can($parcours, 'valider_parcours') || $this->parcoursWorkflow->can(
//                $parcours,
//                'autoriser'
//            ))) {
//            //si on est pas dans un état qui permet de modifier la formation
//            return false;
//        }

        $this->roles = $this->roleRepository->findByPermission($attribute);

        return match ($attribute) {
            self::ROLE_PARCOURS_SHOW_MY => $this->hasShowOnHisParcours($user, $subject),
            self::ROLE_PARCOURS_EDIT_MY => $this->hasEditOnHisParcours($user, $subject),
            default => false,
        };
    }

    private function hasShowOnHisParcours(UserInterface|User $user, Parcours $subject): bool
    {
        //todo: A faire droit sur parcours ou sur la formation
        /** @var User $user */
//        foreach ($user->getUserCentres() as $centre) {
//            if ($centre->getFormation() === $subject && count(array_intersect($centre->getDroits(), $this->roles)) > 0) {
//                return true;
//            }
//        }
//
//        return false;
        return true;
    }

    private function hasEditOnHisParcours(UserInterface|User $user, Formation $subject): bool
    {
//        //todo: A faire droit sur parcours ou sur la formation
//        foreach ($user->getUserCentres() as $centre) {
//            if ($centre->getFormation() === $subject && count(array_intersect($centre->getDroits(), $this->roles)) > 0) {
//                return true;
//            }
//        }
//
//        return false;
        return true;
    }
}
