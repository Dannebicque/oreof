<?php

namespace App\Classes\verif;

use App\Entity\Formation;

class FormationState
{
    //todo: comment exploiter dans Formation ?
    private Formation $formation;

    public function valideStep(mixed $value, Formation $formation): bool|array
    {
        $this->formation = $formation;
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
        return true; //todo: test à ajouter
    }

    private function etatOnglet3(): bool|array
    {
        if ($this->formation->isHasParcours() === null) {
            return ['error' => 'Vous devez indiquez s\'il y a des parcours.'];
        }

        if ($this->formation->isHasParcours() === false) {
            return true;
        }

        if ($this->formation->getParcours()->count() > 0) {
            return true;
        }

        return ['error' => 'Vous devez ajouter au moins un parcours.'];
    }
}
