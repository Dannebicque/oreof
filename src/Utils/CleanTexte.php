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
    public static function cleanTextArea(string $texte): String
    {
        $texte = str_replace(['<br>', '<br/>', '<br />', '<p>', '</p>', '<div>', '</div>', '<span>', '</span>', '<strong>', '</strong>', '<em>', '</em>', '<u>', '</u>', '<i>', '</i>', '<b>', '</b>', '<!--block-->', '<li>', '</li>', '<ul>', '</ul>'], '', $texte);
        $texte = str_replace(['&nbsp;'], ' ', $texte);
        return $texte;
    }
}
