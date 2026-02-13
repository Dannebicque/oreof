<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Workflow/Handler/Handlers/ReouvrirAvantPublieHandler.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 12/02/2026 21:03
 */

namespace App\Workflow\Handler\Handlers;

use App\DTO\Workflow\WorkflowTransitionMetaDto;
use App\Entity\DpeParcours;
use App\Utils\Tools;
use App\Workflow\Handler\AbstractDpeParcoursHandler;
use App\Workflow\Handler\TransitionHandlerInterface;

final class RefuserCommentaireDateHandler extends AbstractDpeParcoursHandler implements TransitionHandlerInterface
{
    public function supports(string $code): bool
    {
        return $code === 'refuser_commentaire_date';
    }

    /**
     * @param array<string, mixed> $data
     */
    public function handle(
        DpeParcours               $dpeParcours,
        WorkflowTransitionMetaDto $metaDto,
        string                    $transition,
        array                     $data
    ): void
    {
        // Récupération safe des champs (2–3 max => simple)
        $argumentaire = (string)($data['argumentaire'] ?? '');
        $date = $data['dateConseil'] ?? null;

        if ($argumentaire === '') {
            throw new \DomainException("Argumentaire obligatoire.");
        }

        if ($date === null) {
            throw new \DomainException("Date obligatoire.");
        }

        //appliquer la transition, gérer l'historique
        $this->dpeParcoursWorkflow->apply($dpeParcours, $transition, [
            'motif' => $argumentaire,
            'date' => $date
        ]);
    }
}
