<?php

namespace App\Service\Apogee\Classes;

class ElementPedagogiDTO4 {

    public string $codElp;

    public function __construct(string $codElp){
        $this->codElp = $codElp;
    }
}