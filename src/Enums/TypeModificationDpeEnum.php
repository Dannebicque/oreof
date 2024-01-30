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
     * Reconduite avec modification MCCC
     * Reconduite avec modification des textes
     * Reconduite avec modification MCCC et des textes
     */
    case CREATION = 'CREATION';
    case NON_OUVERTURE = 'NON_OUVERTURE';
    case RECONDUITE_IDENTIQUE = 'RECONDUITE_IDENTIQUE';
    case RECONDUITE_MODIFICATION_INTITULE = 'RECONDUITE_MODIFICATION_INTITULE';
    case RECONDUITE_MODIFICATION_MCCC = 'RECONDUITE_MODIFICATION_MCCC';
    case RECONDUITE_MODIFICATION_TEXTE = 'RECONDUITE_MODIFICATION_TEXTE';
    case RECONDUITE_MODIFICATION_MCCC_TEXTE = 'RECONDUITE_MODIFICATION_MCCC_TEXTE';

    public function getLibelle(): string
    {
        return match ($this) {
            self::CREATION => 'Création',
            self::NON_OUVERTURE => 'Non ouverture',
            self::RECONDUITE_IDENTIQUE => 'Reconduite identique',
            self::RECONDUITE_MODIFICATION_INTITULE => 'Reconduite avec modification intitulé parcours',
            self::RECONDUITE_MODIFICATION_MCCC => 'Reconduite avec modification MCCC',
            self::RECONDUITE_MODIFICATION_TEXTE => 'Reconduite avec modification des textes',
            self::RECONDUITE_MODIFICATION_MCCC_TEXTE => 'Reconduite avec modification MCCC et des textes',
            default => 'Non défini',
        };
    }

    public function getBadge(): string
    {
        return match ($this) {
            self::CREATION => 'bg-info',
            self::NON_OUVERTURE => 'bg-danger',
            self::RECONDUITE_IDENTIQUE => 'bg-success',
            self::RECONDUITE_MODIFICATION_INTITULE => 'bg-warning',
            self::RECONDUITE_MODIFICATION_MCCC => 'bg-warning',
            self::RECONDUITE_MODIFICATION_TEXTE => 'bg-warning',
            self::RECONDUITE_MODIFICATION_MCCC_TEXTE => 'bg-warning',
            default => 'bg-danger',
        };
    }
}
