<?php

namespace App\Controller;

use App\Classes\UpdateEntity;
use App\Entity\Parcours;
use App\Enums\ModaliteEnseignementEnum;
use App\Enums\RythmeFormationEnum;
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
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        VilleRepository $villeRepository,
        RythmeFormationRepository $rythmeFormationRepository,
        UpdateEntity $updateEntity,
        Request $request,
        Parcours $parcours
    ): Response {
        //todo: check si bonne parcours...
        $data = JsonRequest::getFromRequest($request);
        switch ($data['action']) {
            case 'stateOnglet':
                $method = 'getEtat' . ucfirst($data['onglet']);
                $val = $parcours->$method();

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
            case 'modalitesEnseignement':
                $rep = $updateEntity->saveField($parcours, 'modalitesEnseignement',
                    ModaliteEnseignementEnum::from($data['value']));

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
            case 'int':
                $rep = $updateEntity->saveField($parcours, $data['field'], (int)$data['value']);

                return $this->json($rep);
            case 'etatStep':
                $etatSteps = $parcours->getEtatSteps();
                $step = $data['value'];
                $etatSteps[$step] = $data['isChecked'];
                $parcours->setEtatSteps($etatSteps);

                $entityManager->flush();

                return $this->json(true);
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
