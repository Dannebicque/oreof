<?php

namespace App\Service;

use App\Entity\CentreRestrictedInterface;
use App\Entity\User;

class HelpGrantService
{
    public function isAllowed(CentreRestrictedInterface $entity, ?User $user = null): bool
    {
        if ($user && in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        $centres = $entity->getCentresShow();
        if (!empty($centres)) {
            if (!$user) {
                return false;
            }

            $matched = false;
            foreach ($user->getUserProfils() as $userProfil) {
                $centre = $userProfil->getProfil()?->getCentre()?->value ?? null;
                if ($centre && in_array($centre, $centres, true)) {
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                return false;
            }
        }

        return true;
    }
}
