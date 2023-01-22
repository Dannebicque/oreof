<?php

namespace App\TypeDiplome\Source;

class MasterMeefTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'master_meef';
    public const TEMPLATE = 'master_meef.html.twig';

    public string $libelle = 'Master MEEF';
}
