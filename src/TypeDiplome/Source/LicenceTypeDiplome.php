<?php

namespace App\TypeDiplome\Source;

use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\SemestreParcours;
use App\Entity\Ue;
use PHPStan\PhpDoc\Tag\ParamOutTag;

class LicenceTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'licence';
    public const TEMPLATE = 'licence.html.twig';

    public string $libelle = 'Licence';
    public int $nbSemestres = 6;

    public function initParcours(Parcours $parcours): void
    {
        // TODO: Implement initParcours() method.
    }



}

