<?php

namespace App\Service\Apogee\Classes;

class ComposanteDTO2
{
    /**
     * @var string Code composante associée
     */
    public string $codComposanteAssociee;

    public function __construct(string $codComposanteAssociee){
        $this->codComposanteAssociee = $codComposanteAssociee;
    }
}
