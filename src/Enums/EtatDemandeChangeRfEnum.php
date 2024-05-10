<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Enums/EtatDemandeChangeRfEnum.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/05/2024 18:27
 */

namespace App\Enums;

enum EtatDemandeChangeRfEnum: string implements BadgeEnumInterface
{
    case EN_ATTENTE = 'EN_ATTENTE';
    case VALIDE = 'VALIDE';
    case REFUSE = 'REFUSE';

    public function getLibelle(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'En attente',
            self::VALIDE => 'Validé',
            self::REFUSE => 'Refusé',
            default => 'Non défini',
        };
    }

    public function getBadge(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'bg-warning',
            self::VALIDE => 'bg-success',
            self::REFUSE => 'bg-danger',
            default => 'bg-danger',
        };
    }
}
