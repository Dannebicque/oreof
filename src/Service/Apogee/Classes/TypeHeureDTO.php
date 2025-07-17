<?php

namespace App\Service\Apogee\Classes;

use App\Enums\Apogee\TypeHeureCE;
use Exception;

class TypeHeureDTO
{
    public string $codTypHeure;
    public string $nbrHeureElp;
    public string $normeGroupe;
    public string $nbrMinEtuOuvertureGroupeSupp;

    public function __construct(TypeHeureCE $codTypHeure, string $nbrHeureElp, string $normeGroupe){
        if($codTypHeure instanceof TypeHeureCE === false){
            throw new Exception("Le code type d'heure est invalide. Doit être 'CM', 'TD', ou 'TP' (enum)");
        }
        $this->codTypHeure = $codTypHeure->value;
        $this->nbrHeureElp = $nbrHeureElp;
        $this->normeGroupe = $normeGroupe;
    }
}
