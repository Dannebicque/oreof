<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Utils/CleanTexte.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 11/12/2023 14:41
 */

namespace App\Utils;

abstract class CleanTexte
{
    // Supprime toutes les balises HTML du texte
    public static function cleanTextArea(?string $texte, bool $striptag = true): ?String
    {
        if ($texte === null) {
            return null;
        }

        $texte = str_replace(['<!--block-->'], '', $texte);
        if ($striptag) {
            $texte = strip_tags($texte);
        }
        $texte = str_replace(['&nbsp;'], ' ', $texte);
        return $texte;
    }
}
