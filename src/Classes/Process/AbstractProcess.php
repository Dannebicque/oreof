<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/Process/AbstractProcess.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/09/2023 10:47
 */

namespace App\Classes\Process;

use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AbstractProcess
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected EventDispatcherInterface $eventDispatcher,
        protected TranslatorInterface $translator
    )
    {

    }
}
