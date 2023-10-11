<?php

namespace App\Controller;

use App\Classes\MyGotenbergPdf;
use App\Entity\Parcours;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CompetenceExportController extends AbstractController
{
    #[Route('/competence/export/bcc/{parcours}', name: 'app_competence_export_bcc')]
    public function index(
        MyGotenbergPdf $myGotenbergPdf,
        Parcours $parcours): Response
    {
        return $myGotenbergPdf->render('pdf/bcc_export.html.twig', [
            'formation' => $parcours->getFormation(),
            'parcours' => $parcours,
            'titre' => 'BCC du parcours '.$parcours->getLibelle(),
        ], 'BCC du parcours_'.$parcours->getLibelle().'.pdf');

    }

    #[Route('/competence/export/croise/{parcours}', name: 'app_competence_export_croise')]
    public function croise(
        MyGotenbergPdf $myGotenbergPdf,
        Parcours $parcours): Response
    {
         return $myGotenbergPdf->render('pdf/bcc_export_croise.html.twig', [
             'formation' => $parcours->getFormation(),
             'parcours' => $parcours,
             'titre' => 'BCC du parcours '.$parcours->getLibelle(),
         ], 'BCC Croisé du parcours_'.$parcours->getLibelle());
    }
}
