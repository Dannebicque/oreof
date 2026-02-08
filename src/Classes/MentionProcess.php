<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/ValidationProcess.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 19/07/2023 17:01
 */

namespace App\Classes;

use App\Entity\Formation;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class MentionProcess extends AbstractValidationProcess
{
    //todo: a remplacer ou revoir dans workflow.yaml... ne sert que d'affichage dans l'état...
    public function __construct(KernelInterface $kernel)
    {
        $file = $kernel->getContainer()->getParameter('kernel.project_dir') . '/config/processMention.yaml';

        $data = Yaml::parseFile($file);
        $this->process = $data['process'];
    }

    public function getOptionsForStep(Formation $formation): array
    {
        $enabled = [];

//        $transitions = $this->dpeParcoursWorkflow->getDefinition()->getTransitions();
//        foreach ($transitions as $t) {
//            if ($this->dpeParcoursWorkflow->can($dpeParcours, $t->getName())) {
//                $enabled[] = $t;
//            }
//        }

        $options = [];
        foreach ($this->process as $trans) {
//            $meta = $this->dpeParcoursWorkflow->getMetadataStore()->getTransitionMetadata($trans);
            //array:5 [▼
            //  "icon" => "fa-ballot-check"
            //  "label" => "validation.fiche_matiere"
            //  "isTimeline" => false
            //  "transition" => "en_cours_redaction"
            //  "check" => true
            //]
            $name = $trans['transition'];
            $options[$name] = [
                'label' => $trans['label'] ?? $name,
                'metadata' => $trans,
            ];
        }

        return $options;
    }
}
