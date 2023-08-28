<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/WorkflowExtension.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 19/07/2023 11:11
 */

namespace App\Twig;

use App\Entity\FicheMatiere;
use App\Entity\Formation;
use App\Entity\Parcours;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class WorkflowExtension extends AbstractExtension
{
    public function __construct(
        #[Target('dpe')]
        private readonly WorkflowInterface $dpeWorkflow,
        #[Target('parcours')]
        private readonly WorkflowInterface $parcoursWorkflow,
    ) {
    }


    public function getFunctions(): array
    {
        return [
            new TwigFunction('isPass', [$this, 'isPass']),
            new TwigFunction('isRefuse', [$this, 'isRefuse']),
            new TwigFunction('isPlace', [$this, 'isPlace']),
        ];
    }

    public function isPlace(string $workflow, Parcours|FicheMatiere|Formation $entity, string $place): bool
    {
        $actualPlaces = $this->getWorkflow($workflow)->getMarking($entity)->getPlaces();

        return array_key_exists($place, $actualPlaces);
    }

    public function isPass(string $workflowTexte, Parcours|FicheMatiere|Formation $entity, string $place): bool
    {
        $workflow = $this->getWorkflow($workflowTexte);

        $definition = $workflow->getDefinition();
        $places = array_keys($definition->getPlaces());
        $actualPlaces = $this->getWorkflow($workflowTexte)->getMarking($entity)->getPlaces();

        $indexActualPlace = array_search(array_keys($actualPlaces)[0], $places);
        $indexPlace = array_search($place, $places);

        if ($indexActualPlace > $indexPlace) {
            return true;
        }

        return false;
    }

    public function isRefuse(string $workflow, Parcours|FicheMatiere|Formation $entity): bool
    {
        $places = $this->getWorkflow($workflow)->getMarking($entity)->getPlaces();
        if (count($places) > 0) {
          return str_starts_with(array_keys($places)[0], 'refuse');
        }

        return false;
    }

    private function getWorkflow(string $workflow): WorkflowInterface
    {
        return match($workflow) {
            'dpe' => $this->dpeWorkflow,
            'parcours' => $this->parcoursWorkflow,
        };
    }
}
