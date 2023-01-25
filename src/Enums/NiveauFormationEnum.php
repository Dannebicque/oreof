<?php

namespace App\Enums;

enum NiveauFormationEnum: int
{
//http://lheo.gouv.fr/2.3/lheo/dict-niveaux.html#dict-niveaux
    case  NON_COMMUNIQUE = 0;
    case  SANS_NIVEAU = 1;
    case  NIVEAU_VI = 2;
    case  NIVEAU_V_BIS = 3;
    case  NIVEAU_V = 4;
    case  NIVEAU_IV = 5;
    case  NIVEAU_III = 6;
    case  NIVEAU_II = 7;
    case  NIVEAU_I = 8;

    public function libelle(): string
    {
        return match($this) {
            self::NON_COMMUNIQUE => 'Information non communiquée',
            self::SANS_NIVEAU => 'Sans niveau spécifique',
            self::NIVEAU_VI => 'niveau VI (illettrisme, analphabétisme)',
            self::NIVEAU_V_BIS => 'niveau V bis (préqualification)',
            self::NIVEAU_V => 'niveau V (CAP, BEP, CFPA du premier degré)',
            self::NIVEAU_IV => 'niveau IV (BP, BT, baccalauréat professionnel ou technologique)',
            self::NIVEAU_III => 'niveau III (BTS, DUT)',
            self::NIVEAU_II => 'niveau II (licence ou maîtrise universitaire)',
            self::NIVEAU_I => 'niveau I (supérieur à la maîtrise)',
        };
    }
}
