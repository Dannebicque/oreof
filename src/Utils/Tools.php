<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Utils/Tools.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 22/02/2023 09:06
 */

namespace App\Utils;

use Symfony\Component\String\Slugger\AsciiSlugger;

abstract class Tools
{
    public static function telFormat(?string $number): string
    {
        if (null === $number) {
            return '';
        }

        str_replace(['.', '-', ' '], '', $number);

        if (str_starts_with($number, '33')) {
            $number = '0'.mb_substr($number, 2, mb_strlen($number));
        }

        if (str_starts_with($number, '+33')) {
            $number = '0'.mb_substr($number, 3, mb_strlen($number));
        }

        if (9 === mb_strlen($number)) {
            $number = '0'.$number;
        }

        if (10 === mb_strlen($number)) {
            $str = chunk_split($number, 2, ' ');
        } else {
            $str = str_replace('.', ' ', $number);
        }

        return $str;
    }

    public static function slug(string $texte): string
    {
        return (new AsciiSlugger())->slug($texte);
    }

    public static function convertToFloat(mixed $value): ?float
    {
        $value = trim($value);
        $value = str_replace([',', '.'], '.', $value);

        return (float) $value;
    }
}
