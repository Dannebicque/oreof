<?php

namespace App\Controller;

use App\Classes\GetHistorique;
use App\Classes\JsonReponse;
use App\Classes\Process\FicheMatiereProcess;
use App\Classes\Process\FormationProcess;
use App\Classes\Process\ParcoursProcess;
use App\Classes\ValidationProcess;
use App\Classes\ValidationProcessFicheMatiere;
use App\Entity\FicheMatiere;
use App\Entity\Formation;
use App\Entity\Historique;
use App\Entity\Parcours;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HistoriqueController extends AbstractController
{
    public function __construct(
        private readonly ValidationProcess        $validationProcess,
        private readonly FormationProcess         $formationProcess,
        private readonly ParcoursProcess          $parcoursProcess,
    ) {
    }

    #[Route('/historique/formation/{formation}', name: 'app_historique_formation')]
    public function formation(Formation $formation): Response
    {
        $historiques = [];
        foreach ($formation->getParcours() as $parcours) {
            $histo = $parcours->getHistoriqueParcours();
            foreach ($histo as $h) {
                $historiques[$h->getCreated()?->getTimestamp()] = $h;
            }
        }

        $histo = $formation->getHistoriqueFormations();
        foreach ($histo as $h) {
            $historiques[$h->getCreated()?->getTimestamp()] = $h;
        }
        krsort($historiques);

        return $this->render('historique/_formation.html.twig', [
            'historiques' => $historiques,
            'formation' => $formation,
            'type' => 'formation'
        ]);
    }

    #[Route('/historique/parcours/{parcours}', name: 'app_historique_parcours')]
    public function parcours(Parcours $parcours): Response
    {
        $historiques = [];
        $histo = $parcours->getHistoriqueParcours();
        foreach ($histo as $h) {
            $historiques[$h->getCreated()?->getTimestamp()] = $h;
        }

        $histo = $parcours->getFormation()?->getHistoriqueFormations();
        foreach ($histo as $h) {
            $historiques[$h->getCreated()?->getTimestamp()] = $h;
        }
        krsort($historiques);

        return $this->render('historique/_formation.html.twig', [
            'historiques' => $historiques,
            'parcours' => $parcours,
            'formation' => $parcours->getFormation(),
            'type' => 'parcours'
        ]);
    }

    #[Route('/historique/fiche_matiere/{ficheMatiere}', name: 'app_historique_fiche_matiere')]
    public function fiche_matiere(FicheMatiere $ficheMatiere): Response
    {
        $historiques = [];
        $histo = $ficheMatiere->getHistoriqueFicheMatieres();
        foreach ($histo as $h) {
            $historiques[$h->getCreated()?->getTimestamp()] = $h;
        }

        krsort($historiques);

        return $this->render('historique/_formation.html.twig', [
            'historiques' => $historiques,
            'ficheMatiere' => $ficheMatiere,
            'type' => 'fiche_matiere'
        ]);
    }

    #[Route('/historique/edit/{historique}', name: 'app_historique_edit')]
    public function edit(
        GetHistorique       $getHistorique,
        Historique $historique,
        Request             $request
    ): Response {
        $type = get_class($historique);
        $etape = $historique->getEtape();

        $process = $this->validationProcess->getEtape($etape);
        $objet = $historique->getFormation();

        if ($objet === null) {
            return JsonReponse::error('Formation non trouvée');
        }

        if ($etape === 'cfvu') {
            $laisserPasser = $getHistorique->getHistoriqueFormationLastStep($objet, 'conseil');
        }

        $processData = $this->formationProcess->etatFormation($objet, $process);

        if ($request->isMethod('POST')) {
            return $this->formationProcess->editFormation($historique, $this->getUser(), $etape, $request);
        }

        return $this->render('historique/_edit.html.twig', [
            'process' => $process,
            'type' => $type,
            'etape' => $etape,
            'processData' => $processData ?? null,
            'historique' => $historique,
            'laisserPasser' => $laisserPasser ?? null
        ]);
    }
}
