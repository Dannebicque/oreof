<?php

namespace App\Service\Apogee\Classes;

use InvalidArgumentException;

class TableauComposanteDTO2
{
    /**
     * @var ComposanteDTO2[] $composanteAssociee
     */
    public array $composanteAssociee;

    public function __construct(array $codComposante){
        foreach($codComposante as $code){
            $this->add($code);
        }
    }

    private function add(string $codComposante){
        if(strlen($codComposante) !== 3){
            throw new InvalidArgumentException(
                "Le code de composante associée est invalide. Il doit avoir une longueur de 3 (actuel : "
                . strlen($codComposante) . ")
            ");
        }
        $this->composanteAssociee[] = new ComposanteDTO2($codComposante);
    }
}
