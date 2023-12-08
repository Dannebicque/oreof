<?php

namespace App\DTO;

/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DTO/StatsFichesMatieresParcours.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 07/12/2023 11:42
 */


use App\Entity\ElementConstitutif;

class StatsFichesMatieresParcours
{
    public int $nbFiches = 0;
    public int $nbFichesValideesRP = 0;
    public int $nbFichesValideesRF = 0;
    public int $nbFichesValideesDPE = 0;
    public int $nbFichesCompletes = 0;
    public int $nbFichesNonValidees = 0;

    public function addEc(ElementConstitutif $ec) :void
    {
        if ($ec->getFicheMatiere() !== null) {
            $this->nbFiches++;
            $this->nbFichesNonValidees++;
            if ($ec->getFicheMatiere()->remplissage() === 100.0) {
                $this->nbFichesCompletes++;
            }

            if (in_array('transmis_rf', $ec->getFicheMatiere()->getEtatFiche())) {
                $this->nbFichesValideesRP++;
                $this->nbFichesNonValidees--;
            }
            if (in_array('transmis_dpe', $ec->getFicheMatiere()->getEtatFiche())) {
                $this->nbFichesValideesRF++;
                $this->nbFichesNonValidees--;
            }
            if (in_array('transmis_central', $ec->getFicheMatiere()->getEtatFiche())) {
                $this->nbFichesValideesDPE++;
                $this->nbFichesNonValidees--;
            }
        }

    }
}
