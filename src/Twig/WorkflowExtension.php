<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/WorkflowExtension.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 19/07/2023 11:11
 */

namespace App\Twig;

use App\Classes\GetDpeParcours;
use App\Entity\ChangeRf;
use App\Entity\DpeParcours;
use App\Entity\FicheMatiere;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Enums\TypeModificationDpeEnum;
use App\Utils\Access;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Workflow\WorkflowInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class WorkflowExtension extends AbstractExtension
{
    public function __construct(
        #[Target('fiche')]
        private readonly WorkflowInterface $ficheWorkflow,
        #[Target('dpeParcours')]
        private readonly WorkflowInterface $dpeParcoursWorkflow,
        #[Target('changeRf')]
        private readonly WorkflowInterface $changeRfWorkflow,
    ) {
    }


    public function getFunctions(): array
    {
        return [
            new TwigFunction('isPass', [$this, 'isPass']),
            new TwigFunction('isRefuse', [$this, 'isRefuse']),
            new TwigFunction('isPublie', [$this, 'isPublie']),
            new TwigFunction('isPlace', [$this, 'isPlace']),
            new TwigFunction('isAccessible', [$this, 'isAccessible']),
            new TwigFunction('hasHistorique', [$this, 'hasHistorique']),
            new TwigFunction('hasTransitions', [$this, 'hasTransitions']),
        ];
    }

    public function isAccessible(DpeParcours|Formation $dpeParcours, string $state = 'cfvu'): bool
    {
        if ($dpeParcours instanceof Formation) {
            return Access::isAccessibleMention($dpeParcours, $state);
        }
        return Access::isAccessible($dpeParcours, $state);

    }

    public function hasHistorique(
        Parcours|FicheMatiere|Formation|ChangeRf $entity,
        string                $key,
        array                 $historique
    ): string {
        if (array_key_exists($key, $historique)) {
            return match ($historique[$key]->getEtat()) {
                'valide' => 'btn-success',
                'reserve' => 'btn-warning',
                'laisserPasser' => 'btn-warning',
                'refuse' => 'btn-danger',
                default => 'btn-muted',
            };
        }

        return 'btn-muted';
    }

    public function isPlace(string $workflow, Parcours|FicheMatiere|Formation|ChangeRf $entity, string $place): bool
    {
        //si le workflow est change_rf, supprimer de $place le début changeRf.
        //        if ($workflow === 'changeRf') {
        //            $place = str_replace('changeRf.', '', $place);
        //        }


        $actualPlaces = $this->getPlacesFromEntity($entity, $workflow);
        if ($actualPlaces === false) {
            return false;
        }

        if (array_key_exists($place, $actualPlaces)) {
            return true;
        }

        return false;
    }

    public function isPass(string $workflowTexte, Parcours|FicheMatiere|Formation $entity, string $place): bool
    {
        $actualPlaces = $this->getPlacesFromEntity($entity, $workflowTexte);

        if ($actualPlaces === false) {
            return false;
        }

        $workflow = $this->getWorkflow('parcours');

        $definition = $workflow->getDefinition();
        $places = array_keys($definition->getPlaces());

        $indexActualPlace = array_search(array_keys($actualPlaces)[0], $places);
        $indexPlace = array_search($place, $places);
        return $indexActualPlace > $indexPlace;
    }

    public function isRefuse(string $workflow, Parcours|FicheMatiere|Formation $entity): bool
    {
        $actualPlaces = $this->getPlacesFromEntity($entity, $workflow);
        if ($actualPlaces === false) {
            return false;
        }

        if (count($actualPlaces) > 0) {
            return str_starts_with(array_keys($actualPlaces)[0], 'refuse');
        }

        return false;
    }

    public function isPublie(Parcours|Formation|FicheMatiere $entity, string $type = 'parcours'): bool
    {
        if ($type === 'fiche') {
            $places = $this->getWorkflow('fiche')->getMarking($entity)->getPlaces();
            if (count($places) > 0) {
                return str_starts_with(array_keys($places)[0], 'publie');
            }

            return false;
        }

        if ($type === 'formation') {
            $dpeParcours = GetDpeParcours::getFromFormation($entity); //todo: comment gérer depuis Formation?
        } elseif ($type === 'parcours') {
            $dpeParcours = GetDpeParcours::getFromParcours($entity);
        }
        //passer par le DpeWorkflow

        if (null === $dpeParcours) {
            return false;
        }

        $places = $this->getWorkflow('dpe')->getMarking($dpeParcours)->getPlaces();
        if (count($places) > 0) {
            return str_starts_with(array_keys($places)[0], 'publie');
        }

        return false;
    }

    private function getWorkflow(string $workflow): WorkflowInterface
    {
        return match ($workflow) {
            'dpe' => $this->dpeParcoursWorkflow,
            'parcours' => $this->dpeParcoursWorkflow,
            'fiche' => $this->ficheWorkflow,
            'changeRf' => $this->changeRfWorkflow,
        };
    }

    private function getPlacesFromEntity(Formation|Parcours|FicheMatiere|ChangeRf $entity, string $workflow): array|false
    {
        if ($entity instanceof Parcours) {
            $dpeParcours = GetDpeParcours::getFromParcours($entity);
            if (null === $dpeParcours) {
                return false;
            }
            $actualPlaces = $this->getWorkflow('parcours')->getMarking($dpeParcours)->getPlaces();
        } elseif ($workflow === 'dpe' && $entity instanceof Formation) {
            $dpeParcours = GetDpeParcours::getFromFormation($entity);
            if (null === $dpeParcours) {
                return false;
            }
            $actualPlaces = $this->getWorkflow('parcours')->getMarking($dpeParcours)->getPlaces();
        } else {
            $actualPlaces = $this->getWorkflow($workflow)->getMarking($entity)->getPlaces();
        }

        return $actualPlaces;
    }

    public function hasTransitions(DpeParcours|ChangeRf|FicheMatiere $entity, string $worflow = 'parcours'): array
    {
        $data['valider'] = [];
        $data['reserver'] = [];
        $data['refuser'] = [];

        $transitions = $this->getWorkflow($worflow)->getEnabledTransitions($entity);

        foreach ($transitions as $transition) {
            $meta = $this->getWorkflow($worflow)->getMetadataStore()->getTransitionMetadata($transition);
            if (array_key_exists('type', $meta)) {
                if (array_key_exists('display', $meta) && $meta['display'] === false) {
                    continue;
                }
                $data[$meta['type']][$transition->getName()] = [
                    'transition' => $transition,
                    'meta' => $meta,
                ];
            }
        }
        return $data;
    }
}
