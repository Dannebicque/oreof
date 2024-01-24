<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Enums/TypeParcoursEnum.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 21/12/2023 07:37
 */

namespace App\Enums;

enum TypeParcoursEnum: string
{
    case TYPE_PARCOURS_CLASSIQUE = 'classique';
    case TYPE_PARCOURS_LAS1 = 'las1';
    case TYPE_PARCOURS_LAS23 = 'las23';
    case TYPE_PARCOURS_CPI = 'cpi';
    case TYPE_PARCOURS_ALTERNANCE = 'alternance';

    public function libelle(): string
    {
        return match ($this) {
            self::TYPE_PARCOURS_CLASSIQUE => 'Classique',
            self::TYPE_PARCOURS_LAS1 => 'LAS1',
            self::TYPE_PARCOURS_LAS23 => 'LAS2/LAS3',
            self::TYPE_PARCOURS_CPI => 'CPI',
            self::TYPE_PARCOURS_ALTERNANCE => 'En alternance',
        };
    }

    public function getColor()
    {
        return match ($this) {
            self::TYPE_PARCOURS_CLASSIQUE => 'primary',
            self::TYPE_PARCOURS_LAS1 => 'primary',
            self::TYPE_PARCOURS_LAS23 => 'info',
            self::TYPE_PARCOURS_CPI => 'info',
            self::TYPE_PARCOURS_ALTERNANCE => 'info',
        };
    }

    public function getLabel()
    {
        return match ($this) {
            self::TYPE_PARCOURS_CLASSIQUE => 'Classique',
            self::TYPE_PARCOURS_LAS1 => 'Accès santé 1',
            self::TYPE_PARCOURS_LAS23 => 'Accès santé',
            self::TYPE_PARCOURS_CPI => 'CPI',
            self::TYPE_PARCOURS_ALTERNANCE => 'En alternance',
        };
    }
}
