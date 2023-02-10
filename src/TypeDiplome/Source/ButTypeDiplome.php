<?php

namespace App\TypeDiplome\Source;

class ButTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'but';
    public const TEMPLATE = 'but.html.twig';
    public string $libelle = 'Bachelor Universitaire de Technologie (B.U.T.)';
    public int $nbSemestres = 6;
}
