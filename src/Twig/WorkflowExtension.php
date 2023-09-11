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
use App\Twig\Components\MentionManageComponent;
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
            new TwigFunction('isPublie', [$this, 'isPublie']),
            new TwigFunction('isPlace', [$this, 'isPlace']),
            new TwigFunction('hasHistorique', [$this, 'hasHistorique']),
        ];
    }

    public function hasHistorique(
        Parcours|FicheMatiere|Formation $entity,
        string $key,
        array $historique
    ): string
    {
        if (array_key_exists($key, $historique)) {
            return match($historique[$key]->getEtat())
            {
                'valide' => 'btn-success',
                'reserve' => 'btn-warning',
                'refuse' => 'btn-danger',
                default => 'btn-muted',
            };
        }

        return 'btn-muted';
    }

    public function isPlace(string $workflow, Parcours|FicheMatiere|Formation $entity, string $place): bool
    {

        $actualPlaces = $this->getWorkflow($workflow)->getMarking($entity)->getPlaces();

        if (array_key_exists('en_cours_redaction',$actualPlaces)  && $entity instanceof Formation && $place==='formation') {
            return true;
        }

        if (array_key_exists('soumis_dpe_composante',$actualPlaces)  && $entity instanceof Formation && $place==='dpe') {
            return true;
        }

        if (array_key_exists('soumis_conseil',$actualPlaces)  && $entity instanceof Formation && $place==='conseil') {
            return true;
        }

        if (
            array_key_exists('soumis_central',$actualPlaces)  && $entity instanceof Formation && $place==='ses') {
            return true;
        }

        if (
            array_key_exists('soumis_vp',$actualPlaces)  && $entity instanceof Formation && $place==='vp') {
            return true;
        }

        if (
            array_key_exists('soumis_cfvu',$actualPlaces)  && $entity instanceof Formation && $place==='cfvu') {
            return true;
        }

        if (
            array_key_exists('valide_pour_publication',$actualPlaces)  && $entity instanceof Formation && $place==='publication') {
            return true;
        }

        if (array_key_exists('en_cours_redaction',$actualPlaces) && $entity instanceof Parcours && $place==='parcours') {
            return true;
        }

        if (array_key_exists($place,$actualPlaces)) {
            return true;
        }

        return false;
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

    public function isPublie(Formation|Parcours $entity, string $type): bool
    {
        if ($type === 'formation') {
            $formation = $entity;
        } else if ($type === 'parcours') {
            $formation = $entity->getFormation();
        }


        $places = $this->getWorkflow('dpe')->getMarking($formation)->getPlaces();
        if (count($places) > 0) {
            return str_starts_with(array_keys($places)[0], 'valide_a_publier');
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
