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
    case CREATE = 'create';//todo: possible en maj?
    case EDIT = 'edit';
    case DELETE = 'delete';
    case SHOW = 'show';
    case VALIDATE = 'validate';
    case SUBMIT = 'submit';
    case MANAGE = 'manage';
    case EXPORT = 'export';

    public static function getAvailableTypes()
    {
        return [
            strtoupper(self::CREATE->value),
            strtoupper(self::EDIT->value),
            strtoupper(self::DELETE->value),
            strtoupper(self::SHOW->value),
            strtoupper(self::VALIDATE->value),
            strtoupper(self::SUBMIT->value),
            strtoupper(self::MANAGE->value),
            strtoupper(self::EXPORT->value),
        ];
    }

    public function libelle(): string
    {
        return match ($this) {
            self::CREATE => 'Créer (et modifier)',
            self::EDIT => 'Modifier (sans droit de création)',
            self::DELETE => 'Supprimer',
            self::SHOW => 'Voir',
            self::VALIDATE => 'Valider ou refuser (avec avis ou réserve)',
            self::SUBMIT => 'Soumettre (et passer à l\'étape suivante)',
            self::MANAGE => 'Gérer les droits',
            self::EXPORT => 'Exporter',
        };
    }
}
