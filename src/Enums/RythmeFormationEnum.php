<?php

namespace App\Enums;

enum RythmeFormationEnum: string
{
    case TEMPS_PLEIN = 'Temps Plein';
    case TEMPS_PARTIEL = 'Temps Partiel';
    case TEMPS_MIXTE = 'temps plein et temps partiel';
}
