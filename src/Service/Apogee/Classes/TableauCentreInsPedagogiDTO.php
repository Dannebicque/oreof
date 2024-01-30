<?php

namespace App\Service\Apogee\Classes;

class TableauCentreInsPedagogiDTO
{
    /**
     * @var CentreInsPedagogiDTO[] $centreInsPedagogi
     */
    public array $centreInsPedagogi;

    public function __construct(array $centreInsPedagogi){
        foreach($centreInsPedagogi as $CIP){
            $this->add($CIP);
        }
    }

    private function add(string $codeCIP){
        if(strlen($codeCIP) !== 2){
            throw new \InvalidArgumentException(
                "Le code de centre d'inscription pédagogique (CIP) est invalide. Il doit avoir une longueur de 2 (actuel : " . strlen($codeCIP) . ")"
            );
        }
        $this->centreInsPedagogi[] = new CentreInsPedagogiDTO($codeCIP);
    }
}
