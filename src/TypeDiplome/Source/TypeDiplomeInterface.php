<?php

namespace App\TypeDiplome\Source;

use App\Entity\Formation;

interface TypeDiplomeInterface
{
    public function genereStructure(Formation $formation): void;
}
