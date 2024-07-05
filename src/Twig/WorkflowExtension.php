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
        #[Target('fiche')]
        private readonly WorkflowInterface $ficheWorkflow,
        #[Target('dpeParcours')]
        private readonly WorkflowInterface $dpeParcoursWorkflow,
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
        string                $key,
        array                 $historique
    ): string {
        if ($key === 'vp') { //todo: temporaire pour phase 1 ou VP = SES
            $key = 'ses';
        }

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

    public function isPlace(string $workflow, Parcours|FicheMatiere|Formation $entity, string $place): bool
    {
        $actualPlaces = $this->getPlacesFromEntity($entity, $workflow);

        if ($actualPlaces === false) {
            return false;
        }

        if (array_key_exists('en_cours_redaction', $actualPlaces) && $entity instanceof Formation && $place === 'formation') {
            return true;
        }

        if (array_key_exists('soumis_dpe_composante', $actualPlaces) && $entity instanceof Formation && $place === 'dpe') {
            return true;
        }

        if (array_key_exists('soumis_conseil', $actualPlaces) && $entity instanceof Formation && $place === 'conseil') {
            return true;
        }

        if (
            (array_key_exists('soumis_central', $actualPlaces) && $entity instanceof Formation && ($place === 'ses' || $place === 'vp')) ||
            (array_key_exists('soumis_vp', $actualPlaces) && $entity instanceof Formation && ($place === 'ses' || $place === 'vp'))) {
            //todo: sur cette phase SES et VP sont confondus
            return true;
        }

        if (
            array_key_exists('soumis_cfvu', $actualPlaces) && ($entity instanceof Formation || $entity instanceof Parcours ) && $place === 'cfvu') {
            return true;
        }

        if (
            array_key_exists('valide_pour_publication', $actualPlaces) && $entity instanceof Formation && $place === 'publication') {
            return true;
        }

        if (array_key_exists('en_cours_redaction', $actualPlaces) && $entity instanceof Parcours && $place === 'parcours') {
            return true;
        }

        if (array_key_exists('en_cours_redaction', $actualPlaces) && $entity instanceof FicheMatiere && $place === 'fiche_matiere') {
            return true;
        }

        if (array_key_exists('soumis_parcours', $actualPlaces) && $entity instanceof Parcours && $place === 'parcours_rf') {
            return true;
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

    public function isPublie(Parcours|Formation $entity, string $type): bool
    {
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
            return str_starts_with(array_keys($places)[0], 'valide_a_publier');
        }

        return false;
    }

    private function getWorkflow(string $workflow): WorkflowInterface
    {
        return match ($workflow) {
            'dpe' => $this->dpeParcoursWorkflow,
            'parcours' => $this->dpeParcoursWorkflow,
            'fiche' => $this->ficheWorkflow,
        };
    }

    private function getPlacesFromEntity(Formation|Parcours|FicheMatiere $entity, string $workflow): array|false
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
}
