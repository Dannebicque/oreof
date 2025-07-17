<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Enums/PermissionEnum.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Enums;

enum PermissionEnum: string
{
    case EDIT = 'edit';
    case SHOW = 'show';
    case MANAGE = 'manage';
    case CONSEILLER = 'conseiller';
    case SCOLARITE = 'scolarite';

    public static function getAvailableTypes(): array
    {
        return [
            strtoupper(self::EDIT->value),
            strtoupper(self::SHOW->value),
            strtoupper(self::MANAGE->value),
            strtoupper(self::CONSEILLER->value),
            strtoupper(self::SCOLARITE->value),
        ];
    }

    public function libelle(): string
    {
        return match ($this) {
            self::EDIT => 'Créer, Modifier, supprimer',
            self::SHOW => 'Consultation',
            self::MANAGE => 'Gérer les validations',
            self::SCOLARITE => 'Scolarité',
            self::CONSEILLER => throw new \Exception('To be implemented'),
        };
    }
}
