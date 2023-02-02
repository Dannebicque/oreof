<?php

namespace App\Enums;

enum ModaliteEnseignementEnum : int
{
    case PRESENTIELLE = 0;
    case DISTANCIELLE = 2;
    case HYBRIDE = 1;

    public function libelle(): string
    {
        return match($this) {
            self::PRESENTIELLE => 'Présentielle',
            self::DISTANCIELLE => 'Distancielle',
            self::HYBRIDE => 'Hybride',
        };
    }
}
