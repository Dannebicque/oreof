<?php

namespace App\Controller;

use App\Entity\FicheMatiere;
use App\Entity\Formation;
use App\Entity\Parcours;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HistoriqueController extends AbstractController
{
    #[Route('/historique/formation/{formation}', name: 'app_historique_formation')]
    public function formation(Formation $formation): Response
    {
        return $this->render('historique/_formation.html.twig', [
            'historiques' => $formation->getHistoriqueFormations(),
            'formation' => $formation
        ]);
    }

    #[Route('/historique/parcours/{parcours}', name: 'app_historique_parcours')]
    public function parcours(Parcours $parcours): Response
    {
        $historiques = [];
        $histo = $parcours->getHistoriqueParcours();
        foreach ($histo as $h) {
            $historiques[$h->getCreated()?->format('dmYhis')] = $h ;
        }

        $histo = $parcours->getFormation()?->getHistoriqueFormations();
        foreach ($histo as $h) {
            $historiques[$h->getCreated()?->format('dmYhis')] = $h ;
        }

        asort($historiques);

        return $this->render('historique/_formation.html.twig', [
            'historiques' => $historiques,
            'parcours' => $parcours,
            'formation' => $parcours->getFormation()
        ]);
    }

    #[Route('/historique/fiche_matiere/{fiche_matiere}', name: 'app_historique_fiche_matiere')]
    public function fiche_matiere(FicheMatiere $ficheMatiere): Response
    {
        $historiques = [];
        $histo = $ficheMatiere->getHistoriqueFicheMatieres();
        foreach ($histo as $h) {
            $historiques[$h->getCreated()?->format('dmYhis')] = $h ;
        }

//        $histo = $parcours->getFormation()->getHistoriqueFormations();
//        foreach ($histo as $h) {
//            $historiques[$h->getCreated()->format('dmYhis')] = $h ;
//        }

        asort($historiques);

        return $this->render('historique/_formation.html.twig', [
            'historiques' => $historiques,
            'ficheMatiere' => $ficheMatiere,
            //'formation' => $parcours->getFormation()
        ]);
    }
}
