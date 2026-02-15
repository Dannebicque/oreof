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
use App\Entity\FicheMatiere;
use App\Workflow\Handler\AbstractFicheMatiereHandler;
use App\Workflow\Handler\TransitionFicheHandlerInterface;

final class ValiderFicherHandler extends AbstractFicheMatiereHandler implements TransitionFicheHandlerInterface
{
    public function supports(string $code): bool
    {
        return $code === 'autoriser_fiche';
    }

    /**
     * @param array<string, mixed> $data
     */
    public function handle(
        FicheMatiere              $ficheMatiere,
        WorkflowTransitionMetaDto $metaDto,
        string                    $transition,
        array                     $data
    ): void
    {

        //appliquer la transition, gérer l'historique

        $this->ficheWorkflow->apply($ficheMatiere, $transition, []);
    }
}
