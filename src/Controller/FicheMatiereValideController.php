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
use App\Classes\JsonReponse;
use App\Classes\Process\FicheMatiereProcess;
use App\Classes\ValidationProcessFicheMatiere;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Repository\FicheMatiereRepository;
use App\Repository\FormationRepository;
use App\Repository\ParcoursRepository;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FicheMatiereValideController extends BaseController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/fiche-matiere/valide/formation/{formation}', name: 'fiche_matiere_valide_formation')]
    public function valideFormation(
        CalculStructureParcours $calculStructureParcours,
        Formation $formation
    ): Response {
        $stats = [];
        $parcourss = $formation->getParcours();

        foreach ($parcourss as $parcours) {
            $stats[$parcours->getId()] = $calculStructureParcours->calcul($parcours, false);
            //update des stats sur parcours
            $parcours->setEtatsFichesMatieres($stats[$parcours->getId()]->statsFichesMatieresParcours);
        }

        $this->entityManager->flush();

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

        $stats = $calculStructureParcours->calcul($parcours, false, false);
        $parcours->setEtatsFichesMatieres($stats->statsFichesMatieresParcours);
        $this->entityManager->flush();

        return $this->render('fiche_matiere_valide/valide_parcours.html.twig', [
            'parcours' => $parcours,
            'formation' => $parcours->getFormation(),
            'statsParcours' => $stats,
        ]);
    }

    #[Route('/fiche-matiere/valide/confirmation', name: 'fiche_matiere_valide_valide', methods: ['POST'])]
    public function valideParcoursValide(
        FormationRepository $formationRepository,
        ParcoursRepository   $parcoursRepository,
        CalculStructureParcours $calculStructureParcours,
        ValidationProcessFicheMatiere        $validationProcessFicheMatiere,
        FicheMatiereProcess    $ficheMatiereProcess,
        FicheMatiereRepository $ficheMatiereRepository,
        Request                $request,
    ): Response {
        $type = $request->query->get('type');
        if ('formation' !== $type && 'parcours' !== $type) {
            return JsonReponse::error('Type de validation incorrect');
        }

        $fiches = JsonRequest::getValueFromRequest($request, 'fiches');
        $tFiches = explode(',', $fiches);
        //parcours toutes les fiches du tableau tFches, trouve la fiche et la valide
        foreach ($tFiches as $fiche) {
            $ficheMatiere = $ficheMatiereRepository->find($fiche);

            if (null !== $ficheMatiere && $ficheMatiere->getRemplissage()->isFull()) {
                $ficheMatiereProcess->valideFicheMatiere($ficheMatiere, $this->getUser(), $validationProcessFicheMatiere->getEtape('fiche_matiere'), 'fiche_matiere', $request);
            }
        }

        if ('formation' === $type) {
            $formation = $formationRepository->find($request->query->get('id'));
            if ($formation !== null) {
                $parcourss = $formation->getParcours();
                foreach ($parcourss as $parcours) {
                    $stats = $calculStructureParcours->calcul($parcours, false);
                    $parcours->setEtatsFichesMatieres($stats->statsFichesMatieresParcours);
                }
            }
        } else {
            $parcours = $parcoursRepository->find($request->query->get('id'));
            if ($parcours !== null) {
                $stats = $calculStructureParcours->calcul($parcours, false, false);
                $parcours->setEtatsFichesMatieres($stats->statsFichesMatieresParcours);
            }
        }

        $this->entityManager->flush();

        return JsonReponse::success('Fiches validées');
    }

    #[Route('/fiche-matiere/valide/update', name: 'fiche_matiere_valide_update', methods: ['GET'])]
    public function valideParcoursValideUpdate(
        FormationRepository $formationRepository,
        ParcoursRepository   $parcoursRepository,
        CalculStructureParcours $calculStructureParcours,
        Request                $request,
    ): Response {
        $type = $request->query->get('type');
        if ('formation' !== $type && 'parcours' !== $type) {
            return JsonReponse::error('Type de validation incorrect');
        }

        if ('formation' === $type) {
            $formation = $formationRepository->find($request->query->get('id'));
            if ($formation !== null) {
                $parcourss = $formation->getParcours();
                foreach ($parcourss as $parcours) {
                    $stats = $calculStructureParcours->calcul($parcours, false);
                    $parcours->setEtatsFichesMatieres($stats->statsFichesMatieresParcours);
                }
            }
        } else {
            $parcours = $parcoursRepository->find($request->query->get('id'));
            if ($parcours !== null) {
                $stats = $calculStructureParcours->calcul($parcours, false, false);
                $parcours->setEtatsFichesMatieres($stats->statsFichesMatieresParcours);
            }
        }

        $this->entityManager->flush();
$this->addFlashBag('success', '% mis à jour');

//redirection sur page courage
        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }

}
