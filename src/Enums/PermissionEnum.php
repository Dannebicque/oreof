<?php

namespace App\Enums;

enum PermissionEnum: string
{
    case CREATE = 'create';
    case EDIT = 'edit';
    case DELETE = 'delete';
    case SHOW = 'show';
    case VALIDATE = 'validate';
    case SUBMIT = 'submit';
    case MANAGE = 'manage';
    case EXPORT = 'export';

    public function libelle(): string
    {
        return match($this) {
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
