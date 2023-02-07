<?php

namespace App\Events;

use App\Entity\User;

class UserEvent
{
    protected User $user;
    protected string $centre;
    protected string $idCentre;

    public const USER_DEMANDE_ACCES = 'user.demande_acces';
    public const USER_AJOUTE = 'user.ajoute';
    public const USER_VALIDE_DPE = 'user.valide_dpe';
    public const USER_VALIDE_ADMIN = 'user.valide_admin';

    public function __construct(User $user, string $centre = '', string $idCentre = '')
    {
        $this->user = $user;
        $this->centre = $centre;
        $this->idCentre = $idCentre;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getCentre(): string
    {
        return $this->centre;
    }

    public function getIdCentre(): string
    {
        return $this->idCentre;
    }
}
