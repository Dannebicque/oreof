<?php

namespace App\Utils;

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
}
