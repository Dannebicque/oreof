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

class MentionProcess extends AbstractValidationProcess
{
    public function __construct(KernelInterface $kernel,)
    {
        $file = $kernel->getContainer()->getParameter('kernel.project_dir') . '/config/processMention.yaml';

        $data = Yaml::parseFile($file);
        $this->process = $data['process'];
    }
}
