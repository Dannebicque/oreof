<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Enums/EtatDemandeChangeRfEnum.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/05/2024 18:27
 */

namespace App\Enums;

enum TypeRfEnum: string implements BadgeEnumInterface
{
    case RF = 'RF';
    case CORF = 'CORF';

    public function getLibelle(): string
    {
        return match ($this) {
            self::CORF => 'Co-responsable de formation',
            self::RF => 'Responsable de formation',
            default => 'Non défini',
        };
    }

    public function getBadge(): string
    {
        return match ($this) {
            self::CORF => 'bg-info',
            self::RF => 'bg-success',
            default => 'bg-danger',
        };
    }
}
