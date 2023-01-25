<?php

namespace App\Events;

use App\Entity\User;

class UserEvent
{
    protected User $user;

    public const USER_DEMANDE_ACCES = 'user.demande_acces';
    public const USER_VALIDE_DPE = 'user.valide_dpe';
    public const USER_VALIDE_ADMIN = 'user.valide_admin';

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
