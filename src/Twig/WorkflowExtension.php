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
use Symfony\Component\Workflow\WorkflowInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class WorkflowExtension extends AbstractExtension
{
    public function __construct(
        #[Target('dpe')]
        private readonly WorkflowInterface $dpeWorkflow,
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

    public function isPlace(Parcours|FicheMatiere|Formation $entity, string $place): bool
    {
        $actualPlaces = $this->dpeWorkflow->getMarking($entity)->getPlaces();

        return array_key_exists($place, $actualPlaces);
    }

    public function isPass(string $workflow, Parcours|FicheMatiere|Formation $entity, string $place): bool
    {
        $definition = $this->dpeWorkflow->getDefinition();
        $places = array_keys($definition->getPlaces());
        $actualPlaces = $this->dpeWorkflow->getMarking($entity)->getPlaces();

        $indexActualPlace = array_search(array_keys($actualPlaces)[0], $places);
        $indexPlace = array_search($place, $places);

        if ($indexActualPlace > $indexPlace) {
            return true;
        } else {
            return false;
        }
    }

    public function isRefuse(string $workflow, Parcours|FicheMatiere|Formation $entity): bool
    {
        $places = $this->dpeWorkflow->getMarking($entity)->getPlaces();
        if (count($places) > 0) {
          return str_starts_with(array_keys($places)[0], 'refuse');
        }

        return false;
    }
}
