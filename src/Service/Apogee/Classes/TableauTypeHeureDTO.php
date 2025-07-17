<?php

namespace App\Service\Apogee\Classes;

class TableauTypeHeureDTO
{
    /**
     * @var TypeHeureDTO[] $typeHeure
     */
    public array $typeHeure;

    public function __construct(array $typeHeureArray){
        $this->typeHeure = [];
        foreach($typeHeureArray as $typeHeure){
            $this->add($typeHeure);
        }
    }

    private function add(TypeHeureDTO $typHeureDTO): void
    {
        $this->typeHeure[] = $typHeureDTO;
    }

    public function printInformation(): string
    {
        $result = "";
        foreach($this->typeHeure as $th){
            $result .= "{$th->codTypHeure} {$th->nbrHeureElp}  ";
        }
        return $result;
    }
}
