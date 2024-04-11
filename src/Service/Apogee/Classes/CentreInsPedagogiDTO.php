<?php

namespace App\Service\Apogee\Classes;

class CentreInsPedagogiDTO
{
    public string $codCentreInsPedagogi;

    public function __construct(string $codeCIP){
        $this->codCentreInsPedagogi = $codeCIP;
    }
}
