<?php

namespace App\TypeDiplome\Source;

class LicenceProfessionnelleTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'licence_professionnelle';
    public const TEMPLATE = 'lp.html.twig';

    public string $libelle = 'Licence Professionnelle';
}
