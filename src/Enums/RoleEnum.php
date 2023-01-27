<?php

namespace App\Enums;

enum RoleEnum: string
{
    case ROLE_ADMIN = 'role_admin';
    case ROLE_SES = 'role_ses';
    case ROLE_VP = 'role_vp';
    case ROLE_RESP_DPE = 'role_resp_dpe';
    case ROLE_RESP_FORMATION = 'role_resp_formation';
    case ROLE_RESP_EC = 'role_resp_ec';
    case ROLE_LECTEUR = 'role_lecteur';

    public function libelle(): string
    {
        return match($this) {
            self::ROLE_ADMIN => 'Administrateur',
            self::ROLE_SES => 'SES (Service des enseignements et de la scolarité)',
            self::ROLE_VP => 'VP (Vice-président)',
            self::ROLE_RESP_DPE => 'Responsable DPE composante',
            self::ROLE_RESP_FORMATION => 'Responsable formation',
            self::ROLE_RESP_EC => 'Responsable "EC"',
            self::ROLE_LECTEUR => 'Lecteur',
        };
    }
}
