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

final class TransitionModalViewBuilder
{
    public function __construct(
        // injecte ton service de validation
        // private readonly ParcoursValidationService $validationService,
    )
    {
    }

    public function build(string $transition, DpeParcours $dpeParcours, array $rawMeta): ?TransitionModalView
    {
        // Si on a une entrée view dans les métadata on veut une vue. view contiendra les verifs et éventuellement le template
        if (isset($rawMeta['view'])) {

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
