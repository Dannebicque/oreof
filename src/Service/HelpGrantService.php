<?php

namespace App\Service;

use App\Entity\Help;
use App\Entity\User;

class HelpGrantService
{
    public function isAllowed(Help $help, ?User $user = null): bool
    {
        $centres = $help->getCentresShow();
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
