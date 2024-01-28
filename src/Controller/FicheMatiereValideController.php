<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FicheMatiereValideController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 28/11/2023 10:35
 */

namespace App\Controller;

use App\Classes\Process\FicheMatiereProcess;
use App\Classes\ValidationProcessFicheMatiere;
use App\DTO\StatsFichesMatieresParcours;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Repository\ElementConstitutifRepository;
use App\Repository\FicheMatiereRepository;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\WorkflowInterface;

class FicheMatiereValideController extends BaseController
{
    #[Route('/fiche-matiere/valide/formation/{formation}', name: 'fiche_matiere_valide_formation')]
    public function valideFormation(Formation $formation): Response
    {
        $tabEcs = [];
        $stats = [];
        $parcourss = $formation->getParcours();

        foreach ($parcourss as $parcours) {
            foreach ($parcours->getSemestreParcours() as $semestreParcour) {
                $tabEcs[$parcours->getId()][$semestreParcour->getOrdre()] = [];
                $stats[$parcours->getId()] = new StatsFichesMatieresParcours();

                if ($semestreParcour->getSemestre()->getSemestreRaccroche() !== null) {
                    $semestre = $semestreParcour->getSemestre()->getSemestreRaccroche();
                } else {
                    $semestre = $semestreParcour;
                }

                foreach ($semestre->getSemestre()->getUes() as $ue) {
                    if ($ue->getUeParent() === null) {
                        if ($ue->getUeRaccrochee() !== null) {
                            $ue = $ue->getUeRaccrochee()->getUe();
                        }

                        $tabEcs[$parcours->getId()][$semestreParcour->getOrdre()][$ue->getId()] = [];
                        foreach ($ue->getElementConstitutifs() as $ec) {
                            $tabEcs[$parcours->getId()][$semestreParcour->getOrdre()][$ue->getId()][] = $ec;
                            $stats[$parcours->getId()]->addEc($ec);
                        }
                    }
                    foreach ($ue->getUeEnfants() as $uee) {
                        if ($uee->getUeRaccrochee() !== null) {
                            $uee = $uee->getUeRaccrochee()->getUe();
                        }

                        $tabEcs[$parcours->getId()][$semestreParcour->getOrdre()][$uee->getId()] = [];
                        foreach ($uee->getElementConstitutifs() as $ec) {
                            $tabEcs[$parcours->getId()][$semestreParcour->getOrdre()][$uee->getId()][] = $ec;
                            $stats[$parcours->getId()]->addEc($ec);
                        }
                    }
                }
            }
        }



        return $this->render('fiche_matiere_valide/valide_formation.html.twig', [
            'parcourss' => $parcourss,
            'formation' => $formation,
            'tabParcoursEcs' => $tabEcs,
            'statsParcours' => $stats,
        ]);
    }

    #[Route('/fiche-matiere/valide/parcours/{parcours}', name: 'fiche_matiere_valide_parcours')]
    public function valideParcours(
        Parcours                     $parcours
    ): Response
    {
        $tabEcs = [];

        foreach ($parcours->getSemestreParcours() as $semestreParcour) {
            $tabEcs[$semestreParcour->getOrdre()] = [];

            if ($semestreParcour->getSemestre()->getSemestreRaccroche() !== null) {
                $semestre = $semestreParcour->getSemestre()->getSemestreRaccroche();
            } else {
                $semestre = $semestreParcour;
            }

            foreach ($semestre->getSemestre()->getUes() as $ue) {
                if ($ue->getUeParent() === null) {
                    if ($ue->getUeRaccrochee() !== null) {
                        $ue = $ue->getUeRaccrochee()->getUe();
                    }

                    $tabEcs[$semestreParcour->getOrdre()][$ue->getId()] = [];
                    foreach ($ue->getElementConstitutifs() as $ec) {
                        $tabEcs[$semestreParcour->getOrdre()][$ue->getId()][] = $ec;
                    }
                }
                foreach ($ue->getUeEnfants() as $uee) {
                    if ($uee->getUeRaccrochee() !== null) {
                        $uee = $uee->getUeRaccrochee()->getUe();
                    }

                    $tabEcs[$semestreParcour->getOrdre()][$uee->getId()] = [];
                    foreach ($uee->getElementConstitutifs() as $ec) {
                        $tabEcs[$semestreParcour->getOrdre()][$uee->getId()][] = $ec;
                    }
                }
            }
        }

        return $this->render('fiche_matiere_valide/valide_parcours.html.twig', [
            'parcours' => $parcours,
            'formation' => $parcours->getFormation(),
            'tabEcs' => $tabEcs,
        ]);
    }

    #[Route('/fiche-matiere/valide/confirmation', name: 'fiche_matiere_valide_valide', methods: ['POST'])]
    public function valideParcoursValide(
        ValidationProcessFicheMatiere        $validationProcessFicheMatiere,
        FicheMatiereProcess    $ficheMatiereProcess,
        FicheMatiereRepository $ficheMatiereRepository,
        EntityManagerInterface $entityManager,
        Request                $request,
    ): JsonResponse
    {
        $fiches = JsonRequest::getValueFromRequest($request, 'fiches');
        $tFiches = explode(',', $fiches);
        //parcours toutes les fiches du tableau tFches, trouve la fiche et la valide
        foreach ($tFiches as $fiche) {
            $ficheMatiere = $ficheMatiereRepository->find($fiche);

            if (null !== $ficheMatiere && $ficheMatiere->remplissage() === 100.0) {
                $ficheMatiereProcess->valideFicheMatiere($ficheMatiere, $this->getUser(), $validationProcessFicheMatiere->getEtape('fiche_matiere'), 'fiche_matiere', $request);
            }
        }

        $entityManager->flush();
        return new JsonResponse(['message' => 'ok']);
    }

}
