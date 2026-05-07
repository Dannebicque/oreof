<?php

namespace App\Exception;

use RuntimeException;

class AntivirusException extends RuntimeException {

    const ANTIVIRUS_UNREACHABLE = 3;

    const VIRUS_DETECTED = 5;

    public static function antivirusUnreachableException() {
        return new self("Antivirus is unreachable at the moment", self::ANTIVIRUS_UNREACHABLE);
    }

    public static function virusHasBeenDetectedInFile() {
        return new self("A virus has been detected during the file scan", self::VIRUS_DETECTED);
    }
}