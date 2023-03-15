<?php

namespace App\Controller;

use App\Classes\FormationStructure;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Utils\JsonRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormationStructureController extends BaseController
{
//    /**
//     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
//     */
//    #[Route('/formation/refresh/{formation}', name: 'app_formation_refresh')]
//    public function index(
//        Formation $formation
//    ): Response {
//        if ($formation->isHasParcours() === false) {
//            return $this->redirectToRoute('app_formation_edit', ['id' => $formation->getId(), 'step' => 4]);
//        }
//        return $this->redirectToRoute('app_formation_edit', ['id' => $formation->getId(), 'step' => 3]);
//    }

    #[Route('/formation/structure/pas-parcours/{formation}', name: 'app_formation_genere_structure_pas_parcours')]
    public function genereStructurePasParcours(
        Request $request,
        FormationStructure $formationStructure,
        Formation $formation
    ): Response {

        $formationStructure->genereStructrePasParcours($formation);

        $this->addFlashBag('success', 'La structure de la formation a été générée');

        return $this->json(true);

    }

    #[Route('/formation/structure/{parcours}', name: 'app_formation_genere_structure')]
    public function genereStructure(
        Request $request,
        FormationStructure $formationStructure,
        Parcours $parcours
    ): Response {
        $action = JsonRequest::getValueFromRequest($request, 'action');

        switch ($action) {
            case 'recopieStructure':
                $formationStructure->addParcours($parcours);
                break;
            case 'reinitialiseStructure':
                $formationStructure->genereStructre($parcours);
                break;
            case 'genereStructure':
                $formationStructure->genereStructre($parcours);
                break;
        }


        $this->addFlashBag('success', 'La structure de la formation a été générée');

        return $this->json(true);

    }
}
