<?php

namespace App\Controller;

use App\Classes\ValidationProcess;
use App\Classes\ValidationProcessFicheMatiere;
use App\Entity\Composante;
use App\Repository\ComposanteRepository;
use App\Repository\FicheMatiereRepository;
use App\Repository\FormationRepository;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ValidationController extends BaseController
{
    #[Route('/validation', name: 'app_validation_index')]
    public function index(
        Request              $request,
    ): Response {
        return $this->render('validation/index.html.twig', [
            'step' => $request->query->get('step', 'formation'),
        ]);
    }

    #[Route('/validation/wizard', name: 'app_validation_wizard')]
    public function wizard(
        Request $request,
        ValidationProcessFicheMatiere    $validationProcessFicheMatiere,
        ValidationProcess    $validationProcess,
        ComposanteRepository $composanteRepository,
    ): Response {
        //todo:  Affichage des bons boutons selon le process et le status choisi sur la liste en bas et haut de page.
        //todo: filtrer si DPE composante ou pas
        $idComposante = $request->query->get('composante', null);
        $step = $request->query->get('step', 'formation');
        $composante = null;
        $isSes = $this->isGranted('ROLE_SES');

        if (!$isSes && $idComposante !== null) {
            $composante = $composanteRepository->find($idComposante);
        }

        switch ($step) {
            case 'formation':
                return $this->render('validation/_formation.html.twig', [
                    'ses' => $isSes,
                    'composante' => $composante,
                    'composantes' => $composanteRepository->findAll(),
                    'types_validation' => $validationProcess->getProcess(),
                ]);
            case 'fiche':
                return $this->render('validation/_fiches.html.twig', [
                    'ses' => $isSes,
                    'composante' => $composante,
                    'composantes' => $composanteRepository->findAll(),
                    'types_validation' => $validationProcessFicheMatiere->getProcess(),
                ]);
        }
    }



    #[Route('/validation/composante/{composante}', name: 'app_validation_composante_index')]
    public function composante(
        Request              $request,
        ComposanteRepository $composanteRepository,
        Composante           $composante
    ): Response {
        $step = $request->query->get('step', 'formation');

        return $this->render('validation/index.html.twig', [
            'step' => $step,
            'composante' => $composante->getId(),
            'composantes' => $composanteRepository->findAll(),

        ]);
    }

    #[Route('/validation/liste', name: 'app_validation_formation_liste')]
    public function liste(
        ValidationProcess    $validationProcess,
        ComposanteRepository $composanteRepository,
        FormationRepository  $formationRepository,
        Request              $request
    ): Response {
        $typeValidation = $request->query->get('typeValidation');
        $process = $validationProcess->getEtape($typeValidation);

        if ($request->query->has('composante')) {
            if ($request->query->get('composante') === 'all') {
                $composante = null;
                $formations = $formationRepository->findByTypeValidation($this->getDpe(), $process['transition']);
            } else {
                $composante = $composanteRepository->find($request->query->get('composante'));
                if (!$composante) {
                    throw $this->createNotFoundException('La composante n\'existe pas');
                }
                $formations = $formationRepository->findByComposanteTypeValidation($composante, $this->getDpe(), $process['transition']);
            }




        } else {
            $formations = [];
            $process = null;
        }

        $composantes = $composanteRepository->findAll();
        return $this->render('validation/_liste.html.twig', [
            'process' => $process,
            'formations' => $formations,
            'composantes' => $composantes,
            'etape' => $typeValidation ?? null,
        ]);
    }

    #[Route('/validation/liste/fiche', name: 'app_validation_formation_liste_fiche')]
    public function listeFiche(
        ValidationProcessFicheMatiere    $validationProcessFicheMatiere,
        ComposanteRepository $composanteRepository,
        FicheMatiereRepository  $ficheMatiereRepository,
        Request              $request
    ): Response {
        $typeValidation = $request->query->get('typeValidation');
        $process = $validationProcessFicheMatiere->getEtape($typeValidation);

        if ($request->query->has('composante')) {
            if ($request->query->get('composante') === 'all') {
                $fiches = $ficheMatiereRepository->findByTypeValidation($this->getDpe(), $typeValidation);
            } else {
                $composante = $composanteRepository->find($request->query->get('composante'));
                if (!$composante) {
                    throw $this->createNotFoundException('La composante n\'existe pas');
                }
                $fiches = $ficheMatiereRepository->findByComposanteTypeValidation($composante, $this->getDpe(), $typeValidation);
            }
        } else {
            $fiches = [];
            $process = null;
        }

        $composantes = $composanteRepository->findAll();
        return $this->render('validation/_listeFiches.html.twig', [
            'process' => $process,
            'fiches' => $fiches,
            'composantes' => $composantes,
            'etape' => $typeValidation ?? null,
        ]);
    }
}
