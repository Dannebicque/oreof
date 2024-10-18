<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/ValidationProcess.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 19/07/2023 17:01
 */

namespace App\Classes;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractValidationProcess
{
    protected array $process = [];
    protected array $processAll = [];

    public function getProcess(): array
    {
        return $this->process;
    }

    public function getProcessAll(): array
    {
        return $this->processAll;
    }

    public function getEtapeCle(string $etape, string $cle): string
    {
       if (array_key_exists($etape, $this->process)) {
           return $this->process[$etape][$cle];
       }

         return '';
    }

    public function getEtape(string $etape): array
    {
        if (array_key_exists($etape, $this->process)) {
            return $this->process[$etape];
        }

        return [];
    }
}
