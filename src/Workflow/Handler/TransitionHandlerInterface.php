<?php

namespace App\Workflow\Handler;

use App\DTO\Workflow\WorkflowTransitionMetaDto;
use App\Entity\DpeParcours;

interface TransitionHandlerInterface
{
    public function supports(string $code): bool;

    /** @param array<string, mixed> $data */
    public function handle(DpeParcours               $dpeParcours,
                           WorkflowTransitionMetaDto $metaDto,
                           string                    $transition,
                           array                     $data): void;
}
