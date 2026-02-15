<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Workflow/Handler/AbstractDpeParcoursHandler.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 12/02/2026 19:53
 */

namespace App\Workflow\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Workflow\WorkflowInterface;

class AbstractFicheMatiereHandler
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected WorkflowInterface      $ficheWorkflow
    )
    {
    }
}
