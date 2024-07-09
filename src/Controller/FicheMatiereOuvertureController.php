<?php

namespace App\Controller;

use App\Classes\JsonReponse;
use App\Classes\Process\FicheMatiereProcess;
use App\Classes\ValidationProcessFicheMatiere;
use App\Entity\FicheMatiere;
use App\Events\HistoriqueFicheMatiereEvent;
use App\Events\HistoriqueParcoursEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FicheMatiereOuvertureController extends BaseController
{
    #[Route('/fiche/matiere/demande/ouverture/{id}', name: 'app_fiche_matiere_validation_demande_reouverture')]
    public function ouverture(
        FicheMatiereProcess $ficheMatiereProcess,
        EventDispatcherInterface $eventDispatcher,
        Request $request,
        FicheMatiere $ficheMatiere
    ): Response {
        //vérifier les droits...

        if ($request->isMethod('POST')) {
            $ficheMatiereProcess->ouvertureFicheMatiere($ficheMatiere, $this->getUser(), $request);
            return JsonReponse::success('La fiche matière est réouverte');
        }
        return $this->render('fiche_matiere_ouverture/_reouverture.html.twig', [
            'ficheMatiere' => $ficheMatiere,
        ]);
    }

    #[Route('/fiche/matiere/demande/cloture/{id}', name: 'app_fiche_matiere_validation_demande_cloture')]
    public function cloture(
        ValidationProcessFicheMatiere $validationProcess,
        FicheMatiereProcess $ficheMatiereProcess,
        Request $request,
        FicheMatiere $ficheMatiere
    ): Response {
        if ($request->isMethod('POST')) {
            $process = $validationProcess->getEtape('fiche_matiere');
            $ficheMatiereProcess->valideFicheMatiere($ficheMatiere, $this->getUser(), $process, 'fiche_matiere', $request);
            return JsonReponse::success('La fiche matière est soumise pour validation');
        }

        return $this->render('fiche_matiere_ouverture/_cloture.html.twig', [
            'ficheMatiere' => $ficheMatiere,
        ]);
    }
}
