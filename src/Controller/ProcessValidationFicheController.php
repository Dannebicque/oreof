<?php

namespace App\Controller;

use App\Classes\JsonReponse;
use App\Classes\Process\FicheMatiereProcess;
use App\Classes\ValidationProcessFicheMatiere;
use App\Repository\FicheMatiereRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProcessValidationFicheController extends BaseController
{

    public function __construct(
        private readonly ValidationProcessFicheMatiere        $validationProcessFicheMatiere,
        private readonly FicheMatiereProcess          $ficheMatiereProcess,
    ) {
    }
    #[Route('/validation/fiches/valide-lot/{etape}', name: 'app_validation_valide_fiche_lot')]
    public function valideLot(
        FicheMatiereRepository $ficheMatiereRepository,
        string              $etape,
        Request             $request
    ): Response {
        if ($request->isMethod('POST')) {
            $sFiches = $request->request->get('formations');
        } else {
            $sFiches = $request->query->get('formations');
        }
        $fiches = explode(',', $sFiches);

        $process = $this->validationProcessFicheMatiere->getEtape($etape);
        $tFiches = [];
        foreach ($fiches as $id) {
            $objet = $ficheMatiereRepository->find($id);

            if ($objet === null) {
                return JsonReponse::error('Fiche non trouvée');
            }
            $tFiches[] = $objet;

            $processData = $this->ficheMatiereProcess->etatFicheMatiere($objet, $process);

            if ($request->isMethod('POST')) {
                $this->ficheMatiereProcess->valideFicheMatiere($objet, $this->getUser(), $process, $etape, $request);
            }
        }

        if ($request->isMethod('POST')) {
            $this->toast('success', 'Fiches validées');
            return $this->redirectToRoute('app_validation_index', ['step' => 'fiche']);
        }


        return $this->render('process_validation/_valide_fiches_lot.html.twig', [
            'fiches' => $tFiches,
            'sFiches' => $sFiches,
            'process' => $process,
            'type' => 'lot',
            'id' => $id,
            'etape' => $etape,
            'processData' => $processData ?? null,
        ]);
    }

    #[Route('/validation/fiches/refuse-lot/{etape}', name: 'app_validation_refuse_fiche_lot')]
    public function refuseLot(
        FicheMatiereRepository $ficheMatiereRepository,
        string              $etape,
        Request             $request
    ): Response {
        if ($request->isMethod('POST')) {
            $sFiches = $request->request->get('formations');
        } else {
            $sFiches = $request->query->get('formations');
        }
        $fiches = explode(',', $sFiches);

        $process = $this->validationProcessFicheMatiere->getEtape($etape);
        $tFiches = [];
        foreach ($fiches as $id) {
            $objet = $ficheMatiereRepository->find($id);

            if ($objet === null) {
                return JsonReponse::error('Fiche non trouvée');
            }
            $tFiches[] = $objet;
            $processData = $this->ficheMatiereProcess->etatFicheMatiere($objet, $process);

            if ($request->isMethod('POST')) {
                $this->ficheMatiereProcess->refuseFicheMatiere($objet, $this->getUser(), $process, $etape, $request);
            }
        }

        if ($request->isMethod('POST')) {
            $this->toast('success', 'Fiches refusées');
            return $this->redirectToRoute('app_validation_index', ['step' => 'fiche']);
        }

        return $this->render('process_validation/_refuse_fiches_lot.html.twig', [
            'fiches' => $tFiches,
            'sFiches' => $sFiches,
            'process' => $process,
            'type' => 'lot',
            'id' => $id,
            'etape' => $etape,
            'objet' => $objet,
            'processData' => $processData ?? null,
        ]);
    }

    #[Route('/validation/fiches/reserve-lot/{etape}', name: 'app_validation_reserve_fiche_lot')]
    public function reserveLot(
        FicheMatiereRepository $ficheMatiereRepository,
        string              $etape,
        Request             $request
    ): Response {
        if ($request->isMethod('POST')) {
            $sFiches = $request->request->get('formations');
        } else {
            $sFiches = $request->query->get('formations');
        }
        $fiches = explode(',', $sFiches);

        $process = $this->validationProcessFicheMatiere->getEtape($etape);
        $tFiches = [];
        foreach ($fiches as $id) {
            $objet = $ficheMatiereRepository->find($id);

            if ($objet === null) {
                return JsonReponse::error('Fiche non trouvée');
            }
            $tFiches[] = $objet;
            $processData = $this->ficheMatiereProcess->etatFicheMatiere($objet, $process);

            if ($request->isMethod('POST')) {
                $this->ficheMatiereProcess->reserveFicheMatiere($objet, $this->getUser(), $process, $etape, $request);
            }
        }

        if ($request->isMethod('POST')) {
            $this->toast('success', 'Fiches marquées avec des réserves');
            return $this->redirectToRoute('app_validation_index', ['step' => 'fiche']);
        }

        return $this->render('process_validation/_reserve_fiches_lot.html.twig', [
            'fiches' => $tFiches,
            'sFiches' => $sFiches,
            'process' => $process,
            'objet' => $objet,
            'processData' => $processData ?? null,
            'type' => 'lot',
            'id' => $id,
            'etape' => $etape,
        ]);
    }
}
