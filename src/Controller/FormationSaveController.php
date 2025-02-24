<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FormationSaveController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\FormationStructure;
use App\Classes\UpdateEntity;
use App\Classes\verif\FormationState;
use App\Entity\Formation;
use App\Enums\EtatRemplissageEnum;
use App\Enums\ModaliteEnseignementEnum;
use App\Events\AddCentreFormationEvent;
use App\Repository\ComposanteRepository;
use App\Repository\RythmeFormationRepository;
use App\Repository\UserRepository;
use App\Repository\VilleRepository;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FormationSaveController extends BaseController
{
    public function __construct()
    {
    }


    /**
     * @throws \JsonException
     */
    #[Route('/formation/save/{formation}', name: 'app_formation_save')]
    public function save(
        FormationStructure $formationStructure,
        EventDispatcherInterface $eventDispatcher,
        RythmeFormationRepository $rythmeFormationRepository,
        EntityManagerInterface $em,
        UpdateEntity $updateEntity,
        UserRepository $userRepository,
        VilleRepository $villeRepository,
        ComposanteRepository $composanteRepository,
        Request $request,
        FormationState $formationState,
        Formation $formation
    ): Response {
        $updateEntity->setGroups(['formation:read']);
        $this->denyAccessUnlessGranted('CAN_FORMATION_EDIT_MY', $formation);


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
                return $updateEntity->saveCheckbox(
                    $formation,
                    'localisationMention',
                    $data['value'],
                    $data['isChecked'],
                    $villeRepository
                );
            case 'composanteInscription':
                return $updateEntity->saveCheckbox(
                    $formation,
                    'composantesInscription',
                    $data['value'],
                    $data['isChecked'],
                    $composanteRepository
                );

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

                return $rep;
            case 'textarea':
            case 'selectWithoutEntity':
                return $updateEntity->saveField($formation, $data['field'], $data['value']);
            case 'float':
                return $updateEntity->saveField($formation, $data['field'], (float)$data['value']);
            case 'semestreDebut':
                $semestreInitialDebut = $formation->getSemestreDebut();
                $semestreNouveauDebut = (int)$data['value'];
                $reponse = $updateEntity->saveField($formation, 'semestreDebut', $semestreNouveauDebut);
                $formationStructure->updateStructureDepart($formation, $semestreInitialDebut, $semestreNouveauDebut);
                return $reponse;
            case 'int':
                return $updateEntity->saveField($formation, $data['field'], (int)$data['value']);
            case 'modalitesEnseignement':
                return $updateEntity->saveField(
                    $formation,
                    'modalitesEnseignement',
                    ModaliteEnseignementEnum::from($data['value'])
                );

            case 'rythmeFormation':
                $rythme = $rythmeFormationRepository->find($data['value']);

                return $updateEntity->saveField($formation, 'rythmeFormation', $rythme);

            case 'structureSemestres':
                $tSemestre = $formation->getStructureSemestres();
                $tSemestre[$data['semestre']] = $data['value'];
                $formation->setStructureSemestres($tSemestre);
                $em->flush();

                return $this->json(true);
            case 'coRespFormation':
                $event = new AddCentreFormationEvent($formation, $formation->getCoResponsable(), ['ROLE_CO_RESP_FORMATION']);
                $eventDispatcher->dispatch($event, AddCentreFormationEvent::REMOVE_CENTRE_FORMATION);
                $user = $userRepository->find($data['value']);
                $rep = $updateEntity->saveField($formation, 'coResponsable', $user);
                $event = new AddCentreFormationEvent($formation, $user, ['ROLE_CO_RESP_FORMATION']);
                $eventDispatcher->dispatch($event, AddCentreFormationEvent::ADD_CENTRE_FORMATION);
                return $this->json($rep);
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
                    return $updateEntity->addToArray($formation, $data['field'], $data['value']);
                }

                return $updateEntity->removeToArray($formation, $data['field'], $data['value']);
        }

        return $this->json(['error' => 'action inconnue']);
    }
}
