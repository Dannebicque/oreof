<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Workflow/ModalView/TransitionModalViewBuilder.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 12/02/2026 18:37
 */


namespace App\Workflow\ModalView;

use App\Entity\DpeParcours;
use App\Entity\FicheMatiere;

final class TransitionModalFicheMatiereViewBuilder
{
    public function __construct(
        // injecte ton service de validation
        // private readonly ParcoursValidationService $validationService,
    )
    {
    }

    public function build(string $transition, FicheMatiere $ficheMatiere, array $rawMeta): ?TransitionModalView
    {
        // ✅ règle simple : si form.fields est vide => on veut un report
        if (isset($rawMeta['form']['fields']) && \is_array($rawMeta['form']['fields']) && $rawMeta['form']['fields'] === []) {

            // TODO: remplace par ton service
            $messages = [
                // ['level' => 'error', 'message' => 'UE 3: MCCC manquantes'],
                // ['level' => 'warning', 'message' => 'Description partielle'],
            ];

            $canSubmit = !array_filter($messages, fn($m) => $m['level'] === 'error');

            return new TransitionModalView(
                mode: 'report',
                canSubmit: $canSubmit,
                messages: $messages
            );
        }

        return null; // => affichage normal du form
    }
}
