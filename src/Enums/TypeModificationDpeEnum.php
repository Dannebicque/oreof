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
            self::ATTENTE => 'Attente décision',
            self::CREATION => 'Création',
            self::NON_OUVERTURE => 'Non ouverture',
            self::MODIFICATION => 'modification(s) MCCC, Maquette ou textes',
            self::MODIFICATION_PARCOURS => 'modification des parcours (ajout, suppression)',
            self::MODIFICATION_INTITULE => 'modification intitulé parcours',
            self::MODIFICATION_MCCC => 'modification MCCC et Maquette',
            self::MODIFICATION_TEXTE => 'modification des textes',
            self::MODIFICATION_MCCC_TEXTE => 'modification MCCC, maquettes et des textes',
            self::ANNULATION_REOUVERTURE => 'Annulation de la réouverture',
            default => 'Non défini',
        };
    }

    public function getBadge(): string
    {
        return match ($this) {
            self::ATTENTE => 'bg-primary',
            self::CREATION => 'bg-info',
            self::NON_OUVERTURE => 'bg-danger',
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
}
