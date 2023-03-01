<?php

namespace App\Controller;

use App\Classes\UpdateEntity;
use App\Entity\ElementConstitutif;
use App\Enums\ModaliteEnseignementEnum;
use App\Repository\BlocCompetenceRepository;
use App\Repository\CompetenceRepository;
use App\Repository\LangueRepository;
use App\Repository\TypeEnseignementRepository;
use App\Repository\UserRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ElementConstitutifSaveController extends BaseController
{
    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     * @throws \JsonException
     */
    #[Route('/ec/save/{ec}', name: 'app_ec_save')]
    public function save(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        EntityManagerInterface $entityManager,
        BlocCompetenceRepository $blocCompetenceRepository,
        CompetenceRepository $competenceRepository,
        TypeEnseignementRepository $typeEnseignementRepository,
        UserRepository $userRepository,
        LangueRepository $langueRepository,
        UpdateEntity $updateEntity,
        Request $request,
        ElementConstitutif $ec
    ): Response {
        //todo: check si bonne formation...
        $data = JsonRequest::getFromRequest($request);
        switch ($data['action']) {
            case 'stateOnglet':
                $method = 'getEtat' . ucfirst($data['onglet']);
                $val = $ec->$method();

                return $this->json($val->badge());
            case 'yesNo':
                $rep = $updateEntity->saveYesNo($ec, $data['field'], $data['value']);

                return $this->json($rep);
            case 'mcccs':
                $formation = $ec->getParcours()->getFormation();
                if ($formation === null) {
                    return $this->json(false);
                }
                $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome());
                $typeDiplome->saveMccc($ec, $data['field'], $data['value']);

                return $this->json(true);

            case 'textarea':
            case 'selectWithoutEntity':
                $rep = $updateEntity->saveField($ec, $data['field'], $data['value']);

                return $this->json($rep);
            case 'float':
                $rep = $updateEntity->saveField($ec, $data['field'], (float)$data['value']);

                return $this->json($rep);
            case 'langue':
                $rep = $updateEntity->saveCheckbox($ec, $data['field'], $data['value'],
                    $data['isChecked'],
                    $langueRepository);

                return $this->json($rep);
            case 'typeEnseignement':
                $rythme = $typeEnseignementRepository->find($data['value']);
                $rep = $updateEntity->saveField($ec, 'typeEnseignement', $rythme);

                return $this->json($rep);
            case 'responsableEc':
                $responsableEc = $userRepository->find($data['value']);
                $rep = $updateEntity->saveField($ec, 'responsableEc', $responsableEc);

                return $this->json($rep);
            case 'modalitesEnseignement':
                $rep = $updateEntity->saveField($ec, 'modaliteEnseignement',
                    ModaliteEnseignementEnum::from($data['value']));

                return $this->json($rep);
            case 'int':
                $rep = $updateEntity->saveField($ec, $data['field'], (int)$data['value']);

                return $this->json($rep);
            case 'removeBcc':
                $competences = $ec->getCompetences();

                foreach ($competences as $competence) {
                    if ($competence->getBlocCompetence()?->getId() === $data['value']) {
                        $competence->removeElementConstitutif($ec);
                        $ec->removeCompetence($competence);
                    }
                }
                $entityManager->flush();

                return $this->json(true);
            case 'addCompetence':
                $competence = $competenceRepository->find($data['value']);
                if ($competence !== null) {
                    $ec->addCompetence($competence);
                    $competence->addElementConstitutif($ec);
                    $entityManager->flush();

                    return $this->json(true);
                }

                return $this->json(false);
            case 'array':
                if ($data['isChecked'] === true) {
                    $rep = $updateEntity->addToArray($ec, $data['field'], $data['value']);
                } else {
                    $rep = $updateEntity->removeToArray($ec, $data['field'], $data['value']);

                }

                return $this->json($rep);
            case 'etatStep':
                $etatSteps = $ec->getEtatSteps();
                $step = $data['value'];
                $etatSteps[$step] = $data['isChecked'];
                $ec->setEtatSteps($etatSteps);

                $entityManager->flush();

                return $this->json(true);
        }

        return $this->json(['error' => 'action inconnue']);

    }


}
