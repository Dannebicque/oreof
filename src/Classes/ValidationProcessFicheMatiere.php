<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/ValidationProcess.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 19/07/2023 17:01
 */

namespace App\Classes;

use App\Entity\FicheMatiere;
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
            $this->processAll[$place] = $meta;
            if (array_key_exists('process', $meta) && (bool)$meta['process'] === true) {
                $data[$place] = $meta;
            }
        }

        $this->transitionsAll = [];
        $transitions = $ficheWorkflow->getDefinition()->getTransitions();
        foreach ($transitions as $trans) {
            $this->transitionsAll[$trans->getName()] = $trans;
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

    public function getOptionsForStep(FicheMatiere $ficheMatiere): array
    {
        $enabled = [];

        $transitions = $this->ficheWorkflow->getDefinition()->getTransitions();
        foreach ($transitions as $t) {
            if ($this->ficheWorkflow->can($ficheMatiere, $t->getName())) {
                $enabled[] = $t;
            }
        }

        $options = [];
        foreach ($enabled as $trans) {
            $meta = $this->ficheWorkflow->getMetadataStore()->getTransitionMetadata($trans);
            $name = $trans->getName();
            $options[$name] = [
                'label' => $meta['label'] ?? $name,
                'metadata' => $meta,
            ];
        }

        return $options;
    }
}
