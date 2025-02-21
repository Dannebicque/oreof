<?php

namespace App\Controller;

use App\Classes\JsonReponse;
use App\Classes\Process\FicheMatiereProcess;
use App\Entity\FicheMatiere;
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
}
