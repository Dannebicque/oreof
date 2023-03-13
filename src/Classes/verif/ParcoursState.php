<?php

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
        return true; //todo: test à ajouter
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
