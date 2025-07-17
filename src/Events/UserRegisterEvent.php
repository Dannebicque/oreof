<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Events/UserRegisterEvent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Events;

use App\Entity\Composante;
use App\Entity\Etablissement;
use App\Entity\Formation;
use App\Entity\User;
use App\Enums\CentreGestionEnum;

class UserRegisterEvent
{
    protected User $user;
    protected CentreGestionEnum $centre;
    protected ?Formation $formation = null;
    protected ?Composante $composante = null;
    protected ?Etablissement $etablissement = null;

    public const string USER_DEMANDE_ACCES = 'user.demande_acces';


    public function __construct(User $user, ?CentreGestionEnum $centre = null)
    {
        $this->user = $user;
        $this->centre = $centre;
    }

    public function setFormation(?Formation $formation): void
    {
        $this->formation = $formation;
    }

    public function setComposante(?Composante $composante): void
    {
        $this->composante = $composante;
    }


    public function setEtablissement(?Etablissement $etablissement): void
    {
        $this->etablissement = $etablissement;
    }



    public function getUser(): User
    {
        return $this->user;
    }


    public function getCentre(): CentreGestionEnum
    {
        return $this->centre;
    }


    public function getFormation(): ?Formation
    {
        return $this->formation;
    }


    public function getComposante(): ?Composante
    {
        return $this->composante;
    }


    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }
}
