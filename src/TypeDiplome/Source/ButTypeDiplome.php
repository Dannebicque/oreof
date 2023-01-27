<?php

namespace App\TypeDiplome\Source;

use App\Entity\Formation;
use App\Entity\Parcours;

class ButTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'but';
    public const TEMPLATE = 'but.html.twig';
    public string $libelle = 'Bachelor Universitaire de Technologie (B.U.T.)';

    public function initParcours(Parcours $parcours): void
    {
        // TODO: Implement initParcours() method.
    }

    public function genereStructure(Formation $formation): void
    {
        // TODO: Implement genereStructure() method.
    }
}
