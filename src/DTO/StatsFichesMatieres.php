<?php

namespace App\DTO;

/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DTO/StatsFichesMatieresParcours.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 07/12/2023 11:42
 */


class StatsFichesMatieres
{
    public int $nbFiches = 0;
    public int $nbFichesValidees = 0; //par central
    public int $nbFichesNonValideesSes = 0;
    public int $nbFichesCompletes = 0;
    public int $nbFichesNonValidees = 0;

    public int $nbFichesPubliees = 0;

    public function addStatsParcours(StatsFichesMatieresParcours $statsFichesMatieresParcours): void
    {
        $this->nbFiches += $statsFichesMatieresParcours->nbFiches;
        $this->nbFichesValidees += $statsFichesMatieresParcours->nbFichesValidees;
        $this->nbFichesNonValideesSes += $statsFichesMatieresParcours->nbFichesNonValideesSes;
        $this->nbFichesCompletes += $statsFichesMatieresParcours->nbFichesCompletes;
        $this->nbFichesNonValidees += $statsFichesMatieresParcours->nbFichesNonValidees;
        $this->nbFichesPubliees += $statsFichesMatieresParcours->nbFichesPubliees;
    }
}
