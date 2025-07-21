<?php

namespace App\Service\Apogee\Classes;

class TableauParametrageChargeEnseignementDTO2
{
    /**
     * @var ParametrageAnnuelCeDTO2[] $paramAnnuelCE
     */
    public array $paramAnnuelCE;

    public function __construct(array $paramAnnuelCeArray){
        $this->paramAnnuelCE = [];
        foreach($paramAnnuelCeArray as $paramCE){
            $this->add($paramCE);
        }
    }

    private function add(ParametrageAnnuelCeDTO2 $paramCE): void
    {
        $this->paramAnnuelCE[] = $paramCE;
    }

    public function printInformation(): string
    {
        $result = "";
        foreach($this->paramAnnuelCE as $param){
            $result .= $param->printInformation();
        }
        return $result;
    }
}
