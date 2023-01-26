<?php

namespace App\TypeDiplome\Source;

use App\Entity\Formation;
use App\Entity\Parcours;

interface TypeDiplomeInterface
{
    public function initParcours(Parcours $parcours, Formation $formation): void;
}
