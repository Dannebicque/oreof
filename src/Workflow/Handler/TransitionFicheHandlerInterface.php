<?php

namespace App\Workflow\Handler;

use App\DTO\Workflow\WorkflowTransitionMetaDto;
use App\Entity\DpeParcours;
use App\Entity\FicheMatiere;

interface TransitionFicheHandlerInterface
{
    public function supports(string $code): bool;

    /** @param array<string, mixed> $data */
    public function handle(FicheMatiere              $ficheMatiere,
                           WorkflowTransitionMetaDto $metaDto,
                           string                    $transition,
                           array                     $data): void;
}
