<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Enums/TypeModificationDpeEnum.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 25/01/2024 16:31
 */

namespace App\Enums;

enum TypeModificationDpeEnum: string implements BadgeEnumInterface
{
    /*
     * Création
     * Non ouverture
     * Reconduite identique
     * modification MCCC
     * Reconduite avec modification des textes
     * Reconduite avec modification MCCC et des textes
     */
    case ATTENTE = 'ATTENTE';
    case CREATION = 'CREATION';
    case OUVERT = 'OUVERT';
    case NON_OUVERTURE = 'NON_OUVERTURE';
    case NON_OUVERTURE_SES = 'NON_OUVERTURE_SES';
    case NON_OUVERTURE_CFVU = 'NON_OUVERTURE_CFVU';
    case OUVERTURE_SES = 'OUVERTURE_SES';
    case OUVERTURE_CFVU = 'OUVERTURE_CFVU';
    case MODIFICATION = 'MODIFICATION';
    case MODIFICATION_PARCOURS = 'MODIFICATION_PARCOURS';
    case MODIFICATION_INTITULE = 'MODIFICATION_INTITULE';
    case MODIFICATION_MCCC = 'MODIFICATION_MCCC';
    case MODIFICATION_TEXTE = 'MODIFICATION_TEXTE';
    case MODIFICATION_MCCC_TEXTE = 'MODIFICATION_MCCC_TEXTE';
    case ANNULATION_REOUVERTURE = 'ANNULATION_REOUVERTURE';

    public function getLibelle(): string
    {
        return match ($this) {
            self::OUVERT => 'Ouvert',
            self::ATTENTE => 'Attente décision',
            self::CREATION => 'Création',
            self::NON_OUVERTURE => 'Non ouverture',
            self::NON_OUVERTURE_SES => 'Non ouverture, attente validation SES',
            self::NON_OUVERTURE_CFVU => 'Non ouverture, attente validation CFVU',
            self::OUVERTURE_SES => 'Ouverture, attente validation SES',
            self::OUVERTURE_CFVU => 'Ouverture, attente validation CFVU',
            self::MODIFICATION => 'modification(s) MCCC, Maquette ou textes',
            self::MODIFICATION_PARCOURS => 'modification des parcours (ajout, suppression)',
            self::MODIFICATION_INTITULE => 'modification intitulé parcours',
            self::MODIFICATION_MCCC => 'modification MCCC et Maquette et des textes',
            self::MODIFICATION_TEXTE => 'modification des textes',
            self::MODIFICATION_MCCC_TEXTE => 'modification MCCC, maquettes et des textes',
            self::ANNULATION_REOUVERTURE => 'Annulation de la réouverture',
            default => 'Non défini',
        };
    }

    public function getBadge(): string
    {
        return match ($this) {
            self::OUVERT => 'bg-success',
            self::ATTENTE => 'bg-primary',
            self::CREATION => 'bg-info',
            self::NON_OUVERTURE => 'bg-danger',
            self::NON_OUVERTURE_SES => 'bg-danger',
            self::NON_OUVERTURE_CFVU => 'bg-danger',
            self::OUVERTURE_SES => 'bg-danger',
            self::OUVERTURE_CFVU => 'bg-danger',
            self::MODIFICATION => 'bg-warning',
            self::MODIFICATION_PARCOURS => 'bg-warning',
            self::MODIFICATION_INTITULE => 'bg-warning',
            self::MODIFICATION_MCCC => 'bg-warning',
            self::MODIFICATION_TEXTE => 'bg-warning',
            self::MODIFICATION_MCCC_TEXTE => 'bg-warning',
            self::ANNULATION_REOUVERTURE => 'bg-info',
            default => 'bg-danger',
        };
    }

    public static function listeEtatParcours(): array
    {
        return [
            self::OUVERT,
            self::CREATION,
            self::NON_OUVERTURE,
            self::NON_OUVERTURE_SES,
            self::NON_OUVERTURE_CFVU,
            self::OUVERTURE_SES,
            self::OUVERTURE_CFVU
        ];
    }
}
