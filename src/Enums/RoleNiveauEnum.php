<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Enums/RoleNiveauEnum.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Enums;

/** @deprecated("Profil et centre ?") */
enum RoleNiveauEnum: string
{
    case ETABLISSEMENT = 'ETABLISSEMENT';
    case COMPOSANTE = 'COMPOSANTE';
    case FORMATION = 'FORMATION';
    case PARCOURS = 'PARCOURS';
    case EC = 'EC';
//    case SCOLARITE = 'SCOLARITE';


    public static function getAvailableTypes()
    {
        return [
            self::ETABLISSEMENT->value,
            self::COMPOSANTE->value,
            self::FORMATION->value,
            self::PARCOURS->value,
            self::EC->value,
//            self::SCOLARITE->value,
        ];
    }

    public function libelle(): string
    {
        return match ($this) {
            self::ETABLISSEMENT => 'Etablissement',
            self::COMPOSANTE => 'Composante',
            self::FORMATION => 'Formation',
            self::PARCOURS => 'Parcours',
            self::EC => 'Fiche EC/Matière',
//            self::SCOLARITE => 'Scolarité',
        };
    }
}
