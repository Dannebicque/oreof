<?php

namespace App\Controller;

use App\Classes\GenereSynthese;
use App\Classes\MyGotenbergPdf;
use App\Repository\ComposanteRepository;
use App\Repository\DpeParcoursRepository;
use App\Repository\ParcoursRepository;
use App\Service\VersioningParcours;
use App\Service\VersioningStructure;
use App\TypeDiplome\TypeDiplomeRegistry;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SyntheseModificationController extends BaseController
{
    #[Route('/synthese/modification/pdf', name: 'app_synthese_modification_export_pdf')]
    public function pdf(
        GenereSynthese $genereSynthese,
        ComposanteRepository $composanteRepository,
        MyGotenbergPdf $myGotenbergPdf
    ): Response {
        $composantes = $composanteRepository->findAll();
        $tDemandes = $genereSynthese->getSynthese($composantes, $this->getDpe());


        return $myGotenbergPdf->render('pdf/synthese_modifications.html.twig', [
            'titre' => 'Liste des demande de changement MCCC et maquettes',
            'demandes' => $tDemandes,
            'composantes' => $composantes,
            'dpe' => $this->getDpe(),
        ], 'synthese_changement_cfvu'.(new DateTime())->format('d-m-Y_H-i-s'));

    }
}
