<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Enums/CentreGestionEnum.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Enums;

enum CentreGestionEnum: string implements BadgeEnumInterface
{
    case CENTRE_GESTION_ETABLISSEMENT = 'cg_etablissement';
    case CENTRE_GESTION_COMPOSANTE = 'cg_composante';
    case CENTRE_GESTION_FORMATION = 'cg_formation';
    case CENTRE_GESTION_PARCOURS = 'cg_parcours';
    case CENTRE_GESTION_NULL = '';

    public function libelle(): string
    {
        return match ($this) {
            self::CENTRE_GESTION_NULL => '',
            self::CENTRE_GESTION_ETABLISSEMENT => 'Etablissement',
            self::CENTRE_GESTION_COMPOSANTE => 'Composante',
            self::CENTRE_GESTION_FORMATION => 'Formation',
            self::CENTRE_GESTION_PARCOURS => 'Parcours',
        };
    }

    public function getLibelle(): string
    {
        return match ($this) {
            self::CENTRE_GESTION_NULL => '',
            self::CENTRE_GESTION_ETABLISSEMENT => 'Etablissement',
            self::CENTRE_GESTION_COMPOSANTE => 'Composante',
            self::CENTRE_GESTION_FORMATION => 'Formation',
            self::CENTRE_GESTION_PARCOURS => 'Parcours',
        };
    }

    public function getBadge(): string
    {
        return match ($this) {
            self::CENTRE_GESTION_NULL => '',
            self::CENTRE_GESTION_ETABLISSEMENT => 'badge bg-primary',
            self::CENTRE_GESTION_COMPOSANTE => 'badge bg-success',
            self::CENTRE_GESTION_FORMATION => 'badge bg-warning',
            self::CENTRE_GESTION_PARCOURS => 'badge bg-danger',
        };
    }

    public static function has(string $value): bool
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return true;
            }
        }
        return false;
    }
}
