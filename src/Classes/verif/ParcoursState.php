<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/verif/ParcoursState.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Classes\verif;

use App\Entity\Parcours;

class ParcoursState
{
    //todo: comment exploiter dans Parcours ?
    private Parcours $parcours;

    public function valideStep(mixed $value, Parcours $parcours): bool|array
    {
        $this->parcours = $parcours;
        $method = 'etatOnglet' . ucfirst($value);

        return $this->$method();
    }

    private function etatOnglet0(): bool|array
    {
        return true;//todo: test à ajouter
    }

    private function etatOnglet1(): bool|array
    {
        return true; //todo: test à ajouter
    }

    private function etatOnglet2(): bool|array
    {
        $tab['error'] = [];

        if ($this->parcours->getModalitesEnseignement() === null) {
            $tab['error'][] = 'Vous devez indiquer la modalité des enseignements.';
        }

        if ($this->parcours->isHasStage() === true) {
            if ($this->parcours->getStageText() === null && trim($this->parcours->getStageText()) === '') {
                $tab['error'][] = 'Vous devez indiquer la modalité des stages.';
            }
            if ($this->parcours->getNbHeuresStages() === 0) {
                $tab['error'][] = 'Vous devez indiquer le nombre d\'heures de stages.';
            }
        }

        if ($this->parcours->isHasProjet() === true) {
            if ($this->parcours->getProjetText() === null && trim($this->parcours->getProjetText()) === '') {
                $tab['error'][] = 'Vous devez indiquer la modalité des projets tutorés.';
            }
            if ($this->parcours->getNbHeuresProjet() === 0) {
                $tab['error'][] = 'Vous devez indiquer le nombre d\'heures de projet tutorés.';
            }
        }

        if ($this->parcours->isHasMemoire() === true) {
            if ($this->parcours->getMemoireText() === null && trim($this->parcours->getMemoireText()) === '') {
                $tab['error'][] = 'Vous devez indiquer la modalité des mémoires/TER.';
            }
        }

        return count($tab['error']) > 0 ? $tab : true;
    }

    private function etatOnglet3(): bool|array
    {
        return true; //todo: test à ajouter
    }

    private function etatOnglet4(): bool|array
    {
        return true; //todo: test à ajouter
    }

    private function etatOnglet5(): bool|array
    {
        return true; //todo: test à ajouter
    }

    private function etatOnglet6(): bool|array
    {
        return true; //todo: test à ajouter
    }

    private function etatOnglet7(): bool|array
    {
        return true; //todo: test à ajouter
    }

    private function etatOnglet8(): bool|array
    {
        return true; //todo: test à ajouter
    }
}
