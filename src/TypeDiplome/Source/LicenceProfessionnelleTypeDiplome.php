<?php

namespace App\TypeDiplome\Source;

use App\Entity\Formation;
use App\Entity\Parcours;

class LicenceProfessionnelleTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'licence_professionnelle';
    public const TEMPLATE = 'lp.html.twig';

    public string $libelle = 'Licence Professionnelle';

    public int $nbSemestres = 6;
    public int $nbUes = 0;

}
