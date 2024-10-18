<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/ValidationProcess.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 19/07/2023 17:01
 */

namespace App\Classes;

use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Workflow\WorkflowInterface;

class ValidationProcessFicheMatiere extends AbstractValidationProcess
{
    public function __construct(
        #[Target('fiche')]
        protected WorkflowInterface $ficheWorkflow
    )
    {
        $places = $ficheWorkflow->getDefinition()->getPlaces();
        $data = [];
        foreach ($places as $place) {
            $meta = $ficheWorkflow->getMetadataStore()->getPlaceMetadata($place);
            if (array_key_exists('process', $meta) && (bool)$meta['process'] === true) {
                $data[$place] = $meta;
            }
        }
        $this->process = $data;
    }

    public function getMetaFromTransition(string $transition): array
    {
        $transitions = $this->ficheWorkflow->getDefinition()->getTransitions();
        foreach ($transitions as $trans) {
            if ($trans->getName() === $transition) {
                return $this->ficheWorkflow->getMetadataStore()->getTransitionMetadata($trans);
            }
        }

        return [];
    }
}
