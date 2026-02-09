<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Service/AbstractChecker.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 09/02/2026 11:29
 */

namespace App\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractChecker
{

    /**
     * Règle:
     * - red    : form invalide
     * - orange : form valide mais done=false
     * - green  : form valide et done=true
     */
    public function computeStatus(bool $isComplete, bool $done): string
    {
        if (!$isComplete) {
            return 'red';
        }
        return $done ? 'green' : 'orange';
    }

    protected function allFilled(array $values): bool
    {
        foreach ($values as $v) {
            if ($v === null) {
                return false;
            }
            if (is_string($v) && trim($v) === '') {
                return false;
            }
            if (is_array($v) && count($v) === 0) {
                return false;
            }
        }
        return true;
    }

    protected function filled(mixed $v): bool
    {
        if ($v === null) {
            return false;
        }
        if (\is_string($v)) {
            return trim($v) !== '';
        }
        if (\is_array($v)) {
            return count($v) > 0;
        }
        return true;
    }
}
