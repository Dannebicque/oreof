<?php

namespace App\TypeDiplome\Source;

use App\Entity\Formation;
use App\Entity\Parcours;

interface TypeDiplomeInterface
{
    public function initParcours(Parcours $parcours): void;
    public function genereStructure(Formation $formation): void;
}
