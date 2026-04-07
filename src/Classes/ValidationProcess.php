<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/ValidationProcess.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 19/07/2023 17:01
 */

namespace App\Classes;

use App\Entity\DpeParcours;
use Symfony\Component\Workflow\WorkflowInterface;

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

        $this->transitionsAll = [];
        $transitions = $this->dpeParcoursWorkflow->getDefinition()->getTransitions();
        foreach ($transitions as $trans) {
            $this->transitionsAll[$trans->getName()] = $trans;
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

    public function getOptionsForStep(DpeParcours $dpeParcours): array
    {
        $enabled = [];

        $transitions = $this->dpeParcoursWorkflow->getDefinition()->getTransitions();
        foreach ($transitions as $t) {
            if ($this->dpeParcoursWorkflow->can($dpeParcours, $t->getName())) {
                $enabled[] = $t;
            }
        }

        $options = [];
        foreach ($enabled as $trans) {
            $meta = $this->dpeParcoursWorkflow->getMetadataStore()->getTransitionMetadata($trans);
            $name = $trans->getName();
            $options[$name] = [
                'label' => $meta['label'] ?? $name,
                'metadata' => $meta,
            ];
        }

        return $options;
    }

    public function getNextStepFromPlace(string $currentPlace): ?array
    {
        $transitions = $this->dpeParcoursWorkflow->getDefinition()->getTransitions();

        // On privilégie le flux "normal" de validation quand il existe.
        foreach ($transitions as $transition) {
            if (!in_array($currentPlace, (array)$transition->getFroms(), true)) {
                continue;
            }

            $meta = $this->dpeParcoursWorkflow->getMetadataStore()->getTransitionMetadata($transition);
            if (($meta['type'] ?? null) !== 'valider') {
                continue;
            }

            $to = (array)$transition->getTos();
            if ($to === []) {
                continue;
            }

            return [
                'transition' => $transition->getName(),
                'to' => $to[0],
            ];
        }

        // Fallback: première transition disponible dans la définition.
        foreach ($transitions as $transition) {
            if (!in_array($currentPlace, (array)$transition->getFroms(), true)) {
                continue;
            }

            $to = (array)$transition->getTos();
            if ($to === []) {
                continue;
            }

            return [
                'transition' => $transition->getName(),
                'to' => $to[0],
            ];
        }

        return null;
    }
}
