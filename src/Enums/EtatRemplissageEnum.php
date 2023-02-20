<?php

namespace App\Enums;

enum EtatRemplissageEnum: string
{
    case EN_COURS = 'EN_COURS';
    case VIDE = 'VIDE';
    case COMPLETE = 'COMPLETE';

    public function badge(): string
    {
        return match($this) {
            self::EN_COURS => 'en-cours',
            self::VIDE => 'vide',
            self::COMPLETE => 'complete',
        };
    }
}
