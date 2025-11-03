<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/ValidationProcess.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 19/07/2023 17:01
 */

namespace App\Classes;

use App\Entity\ChangeRf;
use Symfony\Component\Workflow\WorkflowInterface;

class ValidationProcessChangeRf extends AbstractValidationProcess
{
    public function __construct(protected WorkflowInterface $changeRfWorkflow)
    {
        $places = $changeRfWorkflow->getDefinition()->getPlaces();
        $data = [];
        foreach ($places as $place) {
            $meta = $changeRfWorkflow->getMetadataStore()->getPlaceMetadata($place);
            $data[$place] = $meta;
        }
        $this->process = $data;
    }

    public function getMetaFromTransition(string $transition): array
    {
        $transitions = $this->changeRfWorkflow->getDefinition()->getTransitions();
        foreach ($transitions as $trans) {
            if ($trans->getName() === $transition) {
                return $this->changeRfWorkflow->getMetadataStore()->getTransitionMetadata($trans);
            }
        }

        return [];
    }
}
