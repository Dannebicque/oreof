<?php

namespace App\TypeDiplome\Source;

class MasterTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'master';
    public const TEMPLATE = 'master.html.twig';

    public string $libelle = 'Master';
}
