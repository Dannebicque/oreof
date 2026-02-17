<?php

namespace App\Workflow\Handler;

use App\DTO\Workflow\WorkflowTransitionMetaDto;
use App\Entity\DpeParcours;
use App\Entity\User;

interface TransitionHandlerInterface
{
    public function supports(string $code): bool;

    /** @param array<string, mixed> $data */
    public function handle(DpeParcours               $dpeParcours,
                           User $user,
                           WorkflowTransitionMetaDto $metaDto,
                           string                    $transition,
                           array                     $data): void;
}
