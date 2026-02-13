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

final class ValiderConseilHandler extends AbstractDpeParcoursHandler implements TransitionHandlerInterface
{
    public function supports(string $code): bool
    {
        return $code === 'valider_conseil';
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
        $date = $data['dateConseil'] ?? null;
//        $laissezPasser = (string)($data['dateConseil']);
//        $pv = Tools::convertDate((string)($data['dateConseil']));

        if ($date === null) {
            throw new \DomainException("Date obligatoire.");
        }

//        if ($laissezPasser === null && $pv === null) {
//            throw new \DomainException("Au moins une date de laissez-passer ou de PV doit être renseignée.");
//        }
//todo: a gérer...
        //appliquer la transition, gérer l'historique
        $this->dpeParcoursWorkflow->apply($dpeParcours, $transition, [
            'date' => $date
        ]);
    }
}
