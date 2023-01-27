<?php

namespace App\TypeDiplome\Source;

use App\Entity\Formation;
use App\Entity\Parcours;

class MasterMeefTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'master_meef';
    public const TEMPLATE = 'master_meef.html.twig';

    public string $libelle = 'Master MEEF';

    public function initParcours(Parcours $parcours): void
    {
        // TODO: Implement initParcours() method.
    }

    public function genereStructure(Formation $formation): void
    {
        // TODO: Implement genereStructure() method.
    }
}
