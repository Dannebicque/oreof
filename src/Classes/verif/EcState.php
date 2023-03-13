<?php

namespace App\Classes\verif;

use App\Entity\ElementConstitutif;

class EcState
{
    //todo: comment exploiter dans elementConstitutif ?
    private ElementConstitutif $elementConstitutif;

    public function valideStep(mixed $value, ElementConstitutif $elementConstitutif): bool|array
    {
        $this->elementConstitutif = $elementConstitutif;
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
}
