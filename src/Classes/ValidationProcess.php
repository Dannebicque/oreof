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

class ValidationProcess
{
    private array $process;

    public function __construct(KernelInterface $kernel,)
    {
        $file = $kernel->getContainer()->getParameter('kernel.project_dir') . '/config/process.yaml';

        $data = Yaml::parseFile($file);
        $this->process = $data['process'];
    }

    public function getProcess(): array
    {
        return $this->process;
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
