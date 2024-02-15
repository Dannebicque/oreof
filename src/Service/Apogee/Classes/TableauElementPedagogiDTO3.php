<?php

namespace App\Service\Apogee\Classes;

class TableauElementPedagogiDTO3 {

    /**
     * @var ElementPedagogiDTO4[] $elementPedagogi
     */
    public array $elementPedagogi;

    public function __construct(array $tableauElementPedagogique){
        $this->elementPedagogi = [];
        foreach($tableauElementPedagogique as $codElp){
            $this->add($codElp);
        }
    }

    private function add(string $codElp){
        if(mb_strlen($codElp) > 8){
            throw new \Exception("La longueur du code ELP est trop longue (supérieure à 8).");
        }
        $this->elementPedagogi[] = new ElementPedagogiDTO4($codElp);
    }
}