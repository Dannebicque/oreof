<?php

namespace App\TypeDiplome\Source;

use App\Entity\Formation;
use App\Entity\Parcours;

class MasterTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'master';
    public const TEMPLATE = 'master.html.twig';

    public string $libelle = 'Master';

    public function initParcours(Parcours $parcours): void
    {
        // TODO: Implement initParcours() method.
    }

    public function genereStructure(Formation $formation): void
    {
        // TODO: Implement genereStructure() method.
    }
}
