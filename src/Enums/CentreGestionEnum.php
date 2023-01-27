<?php

namespace App\Enums;

enum CentreGestionEnum: string
{
    case CENTRE_GESTION_ETABLISSEMENT = 'cg_etablissement';
    case CENTRE_GESTION_COMPOSANTE = 'cg_composante';
    case CENTRE_GESTION_FORMATION = 'cg_formation';

    public function libelle(): string
    {
        return match($this) {
            self::CENTRE_GESTION_ETABLISSEMENT => 'Etablissement',
            self::CENTRE_GESTION_COMPOSANTE => 'Composante',
            self::CENTRE_GESTION_FORMATION => 'Formation',
        };
    }
}
