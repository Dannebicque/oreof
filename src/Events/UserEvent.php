<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Events/UserEvent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 09/02/2023 07:56
 */

namespace App\Events;

use App\Entity\User;

class UserEvent
{
    protected User $user;
    protected string $centre;
    protected string $motif;
    protected string $idCentre;


    public const string USER_AJOUTE = 'user.ajoute';
    public const string USER_VALIDE_DPE = 'user.valide_dpe';
    public const string USER_VALIDE_ADMIN = 'user.valide_admin';
    public const string USER_REVOQUE_ADMIN = 'user.revoque';
    public const string USER_REFUSER_ADMIN = 'user.refuser';

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

    public function setMotif(string $motif): void
    {
        $this->motif = $motif;
    }

    public function getMotif(): string
    {
        return $this->motif;
    }
}
