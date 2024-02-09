<?php

namespace App\Classes\Apogee;

class TableauElementPedagogiDTO3 {

    /**
     * @var ElementPedagogiDTO4[] $elementPedagogi
     */
    public array $elementPedagogi;

    public function __construct(array $tableauElementPedagogique){
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