<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/ParcoursSaveController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\Bcc;
use App\Classes\UpdateEntity;
use App\Classes\verif\ParcoursState;
use App\Entity\Parcours;
use App\Enums\EtatRemplissageEnum;
use App\Enums\ModaliteEnseignementEnum;
use App\Repository\ComposanteRepository;
use App\Repository\RythmeFormationRepository;
use App\Repository\UserRepository;
use App\Repository\VilleRepository;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParcoursSaveController extends AbstractController
{
    /**
     * @throws \JsonException
     */
    #[Route('/parcours/save/{parcours}', name: 'app_parcours_save')]
    public function save(
        Bcc $bcc,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        ComposanteRepository $composanteRepository,
        EntityManagerInterface $entityManager,
        VilleRepository $villeRepository,
        RythmeFormationRepository $rythmeFormationRepository,
        UpdateEntity $updateEntity,
        ParcoursState $parcoursState,
        Request $request,
        Parcours $parcours
    ): Response {
        //todo: check si bonne parcours...
        $data = JsonRequest::getFromRequest($request);
        $parcoursState->setParcours($parcours);

        switch ($data['action']) {
            case 'stateOnglet':
                $ong = substr($data['onglet'], 6);
                $val = $parcoursState->onglets()[$ong];
                //if $val => en-cours ou vide => désactiver le checkbox
                if ($val === EtatRemplissageEnum::EN_COURS || $val === EtatRemplissageEnum::VIDE) {
                    $etatSteps = $parcours->getEtatSteps();
                    $etatSteps[$ong] = false;
                    $parcours->setEtatSteps($etatSteps);
                    $em->flush();
                }

                return $this->json($val->badge());
            case 'yesNo':
                $rep = $updateEntity->saveYesNo($parcours, $data['field'], $data['value']);

                return $this->json($rep);
            case 'textarea':
            case 'selectWithoutEntity':
                $rep = $updateEntity->saveField($parcours, $data['field'], $data['value']);

                return $this->json($rep);
            case 'float':
                $rep = $updateEntity->saveField($parcours, $data['field'], (float)$data['value']);

                return $this->json($rep);
            case 'recopieBcc':
                $bcc->recopieBcc($parcours, $data['value']);

                return $this->json(true);
            case 'modalitesEnseignement':
                $rep = $updateEntity->saveField(
                    $parcours,
                    'modalitesEnseignement',
                    ModaliteEnseignementEnum::from($data['value'])
                );

                return $this->json($rep);
            case 'rythmeFormation':
                $rythme = $rythmeFormationRepository->find($data['value']);
                $rep = $updateEntity->saveField($parcours, 'rythmeFormation', $rythme);

                return $this->json($rep);
            case 'respParcours':
                $user = $userRepository->find($data['value']);
                $rep = $updateEntity->saveField($parcours, 'respParcours', $user);

                return $this->json($rep);
            case 'localisation':
                $ville = $villeRepository->find($data['value']);
                $rep = $updateEntity->saveField($parcours, 'localisation', $ville);

                return $this->json($rep);
            case 'composanteInscription':
                $composante = $composanteRepository->find($data['value']);
                $rep = $updateEntity->saveField($parcours, 'composanteInscription', $composante);

                return $this->json($rep);
            case 'int':
                $rep = $updateEntity->saveField($parcours, $data['field'], (int)$data['value']);

                return $this->json($rep);
            case 'etatStep':
                $valideState = (bool)$data['isChecked'] === true ? $parcoursState->valideStep(
                    $data['value']
                ) : true;

                if ($valideState === true) {
                    $etatSteps = $parcours->getEtatSteps();
                    $step = $data['value'];
                    $etatSteps[$step] = $data['isChecked'];
                    $parcours->setEtatSteps($etatSteps);

                    $em->flush();

                    return $this->json(true);
                }

                return $this->json($valideState);
            case 'array':
                if ($data['isChecked'] === true) {
                    $rep = $updateEntity->addToArray($parcours, $data['field'], $data['value']);
                } else {
                    $rep = $updateEntity->removeToArray($parcours, $data['field'], $data['value']);
                }

                return $this->json($rep);
        }

        return $this->json(['error' => 'action inconnue']);
    }
}
