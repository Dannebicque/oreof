<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FormationSaveController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\UpdateEntity;
use App\Classes\verif\FormationState;
use App\Entity\Formation;
use App\Enums\EtatRemplissageEnum;
use App\Enums\ModaliteEnseignementEnum;
use App\Repository\ComposanteRepository;
use App\Repository\RythmeFormationRepository;
use App\Repository\VilleRepository;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;

class FormationSaveController extends BaseController
{
    public function __construct(private WorkflowInterface $dpeWorkflow)
    {
    }


    /**
     * @throws \JsonException
     */
    #[Route('/formation/save/{formation}', name: 'app_formation_save')]
    public function save(
        RythmeFormationRepository $rythmeFormationRepository,
        EntityManagerInterface $em,
        UpdateEntity $updateEntity,
        VilleRepository $villeRepository,
        ComposanteRepository $composanteRepository,
        Request $request,
        FormationState $formationState,
        Formation $formation
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_FORMATION_EDIT_MY', $formation);

//        if (!($this->dpeWorkflow->can($formation, 'valide_rf') || $this->dpeWorkflow->can($formation, 'autoriser'))&& !$this->isGranted('ROLE_SES')) {
//            //si on est pas dans un état qui permet de modifier la formation
//            return $this->json('Vous ne pouvez plus modifier cette formation', Response::HTTP_FORBIDDEN);
//        }
//
//        if ($this->dpeWorkflow->can($formation, 'autoriser')) {
//            //un champ est modifié, on met à jour l'état
//            $this->dpeWorkflow->apply($formation, 'autoriser');
//        }

        $data = JsonRequest::getFromRequest($request);
        $formationState->setFormation($formation);
        switch ($data['action']) {
            case 'stateOnglet':
                $ong = substr($data['onglet'], 6);
                $val = $formationState->onglets()[$ong];
                //if $val => en-cours ou vide => désactiver le checkbox
                if ($val === EtatRemplissageEnum::EN_COURS || $val === EtatRemplissageEnum::VIDE) {
                    $etatSteps = $formation->getEtatSteps();
                    $etatSteps[$ong] = false;
                    $formation->setEtatSteps($etatSteps);
                    $em->flush();
                }

                return $this->json($val->badge());
            case 'ville':
                $rep = $updateEntity->saveCheckbox(
                    $formation,
                    'localisationMention',
                    $data['value'],
                    $data['isChecked'],
                    $villeRepository
                );

                return $this->json($rep);
            case 'composanteInscription':
                $rep = $updateEntity->saveCheckbox(
                    $formation,
                    'composantesInscription',
                    $data['value'],
                    $data['isChecked'],
                    $composanteRepository
                );

                return $this->json($rep);
            case 'yesNo':
                $rep = $updateEntity->saveYesNo($formation, $data['field'], $data['value']);
                if ($data['field'] === 'hasParcours') {
                    foreach ($formation->getParcours() as $parcours) {
                        if ($parcours->isParcoursDefaut()) {
                            $parcours->setLibelle('[A renomer] ' . $parcours->getLibelle());
                        }
                    }
                    $em->flush();
                }

                return $this->json($rep);
            case 'textarea':
            case 'selectWithoutEntity':
                $rep = $updateEntity->saveField($formation, $data['field'], $data['value']);

                return $this->json($rep);
            case 'float':
                $rep = $updateEntity->saveField($formation, $data['field'], (float)$data['value']);

                return $this->json($rep);
            case 'int':
                $rep = $updateEntity->saveField($formation, $data['field'], (int)$data['value']);

                return $this->json($rep);
            case 'modalitesEnseignement':
                $rep = $updateEntity->saveField(
                    $formation,
                    'modalitesEnseignement',
                    ModaliteEnseignementEnum::from($data['value'])
                );

                return $this->json($rep);
            case 'rythmeFormation':
                $rythme = $rythmeFormationRepository->find($data['value']);
                $rep = $updateEntity->saveField($formation, 'rythmeFormation', $rythme);

                return $this->json($rep);
            case 'structureSemestres':
                $tSemestre = $formation->getStructureSemestres();
                $tSemestre[$data['semestre']] = $data['value'];
                $formation->setStructureSemestres($tSemestre);
                $em->flush();

                return $this->json(true);
            case 'etatStep':
                $valideState = (bool)$data['isChecked'] === true ? $formationState->valideStep(
                    $data['value']
                ) : true;

                if ($valideState === true) {
                    $etatSteps = $formation->getEtatSteps();
                    $step = $data['value'];
                    $etatSteps[$step] = $data['isChecked'];
                    $formation->setEtatSteps($etatSteps);

                    $em->flush();

                    return $this->json(true);
                }

                return $this->json($valideState);
            case 'array':
                if ($data['isChecked'] === true) {
                    $rep = $updateEntity->addToArray($formation, $data['field'], $data['value']);
                } else {
                    $rep = $updateEntity->removeToArray($formation, $data['field'], $data['value']);
                }

                return $this->json($rep);
        }

        return $this->json(['error' => 'action inconnue']);
    }
}
