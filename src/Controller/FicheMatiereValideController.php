<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FicheMatiereValideController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 28/11/2023 10:35
 */

namespace App\Controller;

use App\Classes\CalculStructureParcours;
use App\Classes\Process\FicheMatiereProcess;
use App\Classes\ValidationProcessFicheMatiere;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Repository\FicheMatiereRepository;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FicheMatiereValideController extends BaseController
{
    #[Route('/fiche-matiere/valide/formation/{formation}', name: 'fiche_matiere_valide_formation')]
    public function valideFormation(
        CalculStructureParcours $calculStructureParcours,
        Formation $formation): Response
    {
        $stats = [];
        $parcourss = $formation->getParcours();

        foreach ($parcourss as $parcours) {
            $stats[$parcours->getId()] = $calculStructureParcours->calcul($parcours, false);
        }

        return $this->render('fiche_matiere_valide/valide_formation.html.twig', [
            'parcourss' => $parcourss,
            'formation' => $formation,
            'statsParcours' => $stats,
        ]);
    }

    #[Route('/fiche-matiere/valide/parcours/{parcours}', name: 'fiche_matiere_valide_parcours')]
    public function valideParcours(
        CalculStructureParcours $calculStructureParcours,
        Parcours                     $parcours
    ): Response {

        return $this->render('fiche_matiere_valide/valide_parcours.html.twig', [
            'parcours' => $parcours,
            'formation' => $parcours->getFormation(),
            'statsParcours' => $calculStructureParcours->calcul($parcours, false, false),
        ]);
    }

    #[Route('/fiche-matiere/valide/confirmation', name: 'fiche_matiere_valide_valide', methods: ['POST'])]
    public function valideParcoursValide(
        ValidationProcessFicheMatiere        $validationProcessFicheMatiere,
        FicheMatiereProcess    $ficheMatiereProcess,
        FicheMatiereRepository $ficheMatiereRepository,
        EntityManagerInterface $entityManager,
        Request                $request,
    ): JsonResponse {
        $fiches = JsonRequest::getValueFromRequest($request, 'fiches');
        $tFiches = explode(',', $fiches);
        //parcours toutes les fiches du tableau tFches, trouve la fiche et la valide
        foreach ($tFiches as $fiche) {
            $ficheMatiere = $ficheMatiereRepository->find($fiche);

            if (null !== $ficheMatiere && $ficheMatiere->getRemplissage()->isFull()) {
                $ficheMatiereProcess->valideFicheMatiere($ficheMatiere, $this->getUser(), $validationProcessFicheMatiere->getEtape('fiche_matiere'), 'fiche_matiere', $request);
            }
        }

        $entityManager->flush();
        return new JsonResponse(['message' => 'ok']);
    }

}
