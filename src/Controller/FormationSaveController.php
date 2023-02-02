<?php

namespace App\Controller;

use App\Classes\UpdateEntity;
use App\Entity\Formation;
use App\Enums\ModaliteEnseignementEnum;
use App\Repository\ComposanteRepository;
use App\Repository\RythmeFormationRepository;
use App\Repository\VilleRepository;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormationSaveController extends BaseController
{
    #[Route('/formation/save/{formation}', name: 'app_formation_save')]
    public function save(
        RythmeFormationRepository $rythmeFormationRepository,
        EntityManagerInterface $em,
        UpdateEntity $updateEntity,
        VilleRepository $villeRepository,
        ComposanteRepository $composanteRepository,
        Request $request,
        Formation $formation
    ): Response {
        //todo: check si bonne formation...
        $data = JsonRequest::getFromRequest($request);
        switch ($data['action']) {
            case 'ville':
                $rep = $updateEntity->saveCheckbox($formation, 'localisationMention', $data['value'],
                    $data['isChecked'],
                    $villeRepository);

                return $this->json($rep);
            case 'composanteInscription':
                $rep = $updateEntity->saveCheckbox($formation, 'composantesInscription', $data['value'],
                    $data['isChecked'],
                    $composanteRepository);

                return $this->json($rep);
            case 'yesNo':
                $rep = $updateEntity->saveYesNo($formation, $data['field'], $data['value']);

                return $this->json($rep);
            case 'textarea':
            case 'selectWithoutEntity':
                $rep = $updateEntity->saveField($formation, $data['field'], $data['value']);

                return $this->json($rep);
            case 'float':
                $rep = $updateEntity->saveField($formation, $data['field'], (float)$data['value']);

                return $this->json($rep);
            case 'modalitesEnseignement':
                $rep = $updateEntity->saveField($formation, 'modalitesEnseignement',
                    ModaliteEnseignementEnum::from($data['value']));

                return $this->json($rep);
            case 'rythmeFormation':
                $rythme = $rythmeFormationRepository->find($data['value']);
                $rep = $updateEntity->saveField($formation, 'rythmeFormation', $rythme);

                return $this->json($rep);
            case 'int':
                $rep = $updateEntity->saveField($formation, $data['field'], (int)$data['value']);

                return $this->json($rep);
            case 'structureSemestres':
                $tSemestre = $formation->getStructureSemestres();
                $tSemestre[$data['semestre']] = $data['value'];
                $formation->setStructureSemestres($tSemestre);
                $em->flush();

                return $this->json(true);

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
