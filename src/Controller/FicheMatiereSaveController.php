<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FicheMatiereSaveController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\UpdateEntity;
use App\Classes\verif\FicheMatiereState;
use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Enums\EtatRemplissageEnum;
use App\Enums\ModaliteEnseignementEnum;
use App\Repository\BlocCompetenceRepository;
use App\Repository\CompetenceRepository;
use App\Repository\LangueRepository;
use App\Repository\NatureUeEcRepository;
use App\Repository\UserRepository;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FicheMatiereSaveController extends BaseController
{
    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     * @throws \JsonException
     */
    #[Route('/fiche_matiere/save/{ficheMatiere}', name: 'app_fiche_matiere_save')]
    public function save(
        FicheMatiereState $ficheMatiereState,
        EntityManagerInterface $entityManager,
        CompetenceRepository $competenceRepository,
        UserRepository $userRepository,
        LangueRepository $langueRepository,
        UpdateEntity $updateEntity,
        Request $request,
        FicheMatiere $ficheMatiere
    ): Response {
        $ficheMatiereState->setFicheMatiere($ficheMatiere);

        if (str_ends_with($ficheMatiere->getLibelle(), '(à compléter)')) {
            $ficheMatiere->setLibelle(trim(str_replace('(à compléter)','',$ficheMatiere->getLibelle())));
            $entityManager->flush();
        }

        //todo: check si bonne formation...
        $data = JsonRequest::getFromRequest($request);
        switch ($data['action']) {
            case 'stateOnglet':
                $ong = substr($data['onglet'], 6);
                $val = $ficheMatiereState->onglets()[$ong];
                //if $val => en-cours ou vide => désactiver le checkbox
                if ($val === EtatRemplissageEnum::EN_COURS || $val === EtatRemplissageEnum::VIDE) {
                    $etatSteps = $ficheMatiere->getEtatSteps();
                    $etatSteps[$ong] = false;
                    $ficheMatiere->setEtatSteps($etatSteps);
                    $entityManager->flush();
                }

                return $this->json($val->badge());
            case 'yesNo':
                $rep = $updateEntity->saveYesNo($ficheMatiere, $data['field'], $data['value']);

                return $this->json($rep);
            case 'textarea':
            case 'selectWithoutEntity':
                $rep = $updateEntity->saveField($ficheMatiere, $data['field'], $data['value']);

                return $this->json($rep);
            case 'float':
                $rep = $updateEntity->saveField($ficheMatiere, $data['field'], (float)$data['value']);

                return $this->json($rep);
            case 'langue':
                $rep = $updateEntity->saveCheckbox(
                    $ficheMatiere,
                    $data['field'],
                    $data['value'],
                    $data['isChecked'],
                    $langueRepository
                );

                return $this->json($rep);
            case 'responsableFicheMatiere':
                $responsableFicheMatiere = $userRepository->find($data['value']);
                $rep = $updateEntity->saveField($ficheMatiere, 'responsableFicheMatiere', $responsableFicheMatiere);

                return $this->json($rep);
            case 'modalitesEnseignement':
                $rep = $updateEntity->saveField(
                    $ficheMatiere,
                    'modaliteEnseignement',
                    ModaliteEnseignementEnum::from($data['value'])
                );

                return $this->json($rep);
            case 'int':
                $rep = $updateEntity->saveField($ficheMatiere, $data['field'], (int)$data['value']);

                return $this->json($rep);
            case 'removeBcc':
                $competences = $ficheMatiere->getCompetences();

                foreach ($competences as $competence) {
                    if ($competence->getBlocCompetence()?->getId() === $data['value']) {
                        $competence->removeFicheMatiere($ficheMatiere);
                        $ficheMatiere->removeCompetence($competence);
                    }
                }
                $entityManager->flush();

                return $this->json(true);
            case 'addCompetence':
                $competence = $competenceRepository->find($data['value']);
                if ($competence !== null) {
                    $ficheMatiere->addCompetence($competence);
                    $competence->addFicheMatiere($ficheMatiere);
                    $entityManager->flush();

                    return $this->json(true);
                }

                return $this->json(false);
            case 'array':
                if ($data['isChecked'] === true) {
                    $rep = $updateEntity->addToArray($ficheMatiere, $data['field'], $data['value']);
                } else {
                    $rep = $updateEntity->removeToArray($ficheMatiere, $data['field'], $data['value']);
                }

                return $this->json($rep);
            case 'etatStep':
                $valideState = (bool)$data['isChecked'] === true ? $ficheMatiereState->valideStep(
                    $data['value']
                ) : true;

                if ($valideState === true) {
                    $etatSteps = $ficheMatiere->getEtatSteps();
                    $step = $data['value'];
                    $etatSteps[$step] = $data['isChecked'];
                    $ficheMatiere->setEtatSteps($etatSteps);

                    $entityManager->flush();

                    return $this->json(true);
                }

                return $this->json($valideState);
        }

        return $this->json(['error' => 'action inconnue']);
    }
}
