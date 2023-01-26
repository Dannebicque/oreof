<?php

namespace App\TypeDiplome\Source;

use App\Entity\Formation;
use App\Entity\Parcours;

class ButTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'but';
    public const TEMPLATE = 'but.html.twig';
    public string $libelle = 'Bachelor Universitaire de Technologie (B.U.T.)';

    public function initParcours(Parcours $parcours, Formation $formation): void
    {
        // TODO: Implement initParcours() method.
    }
}
