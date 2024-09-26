<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/ValidationProcess.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 19/07/2023 17:01
 */

namespace App\Classes;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\Yaml\Yaml;

class ValidationProcess extends AbstractValidationProcess
{
    public function __construct(protected WorkflowInterface $dpeParcoursWorkflow)
    {
        $places = $dpeParcoursWorkflow->getDefinition()->getPlaces();
        $data = [];
        foreach ($places as $place) {
            $meta = $dpeParcoursWorkflow->getMetadataStore()->getPlaceMetadata($place);
            if (array_key_exists('process', $meta) && (bool)$meta['process'] === true) {
                $data[$place] = $meta;
            }

            $dataAll[$place] = $meta;
        }
        $this->processAll = $dataAll;
        $this->process = $data;
    }

    public function getMetaFromTransition(string $transition): array
    {
        $transitions = $this->dpeParcoursWorkflow->getDefinition()->getTransitions();
        foreach ($transitions as $trans) {
            if ($trans->getName() === $transition) {
                return $this->dpeParcoursWorkflow->getMetadataStore()->getTransitionMetadata($trans);
            }
        }

        return [];
    }
}
