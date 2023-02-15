<?php

namespace App\Enums;

enum RoleNiveauEnum: string
{
   case ETABLISSEMENT = 'ETABLISSEMENT';
   case COMPOSANTE = 'COMPOSANTE';
   case FORMATION = 'FORMATION';
   case EC = 'EC';
   case SCOLARITE = 'SCOLARITE';


    public function libelle(): string
    {
        return match($this) {
            self::ETABLISSEMENT => 'Etablissement',
            self::COMPOSANTE => 'Composante',
            self::FORMATION => 'Formation',
            self::EC => 'Element Constitutif',
            self::SCOLARITE => 'Scolarité',
        };
    }
}
