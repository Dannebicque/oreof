<?php

namespace App\Controller;

use App\Entity\Parcours;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CompetenceExportController extends AbstractController
{
    #[Route('/competence/export/bcc/{parcours}', name: 'app_competence_export_bcc')]
    public function index(Parcours $parcours): Response
    {
        $html = $this->renderView('pdf/bcc_export.html.twig', [
            'formation' => $parcours->getFormation(),
            //'typeDiplome' => $typeDiplome,
            'parcours' => $parcours,
            'titre' => 'BCC du parcours '.$parcours->getLibelle(),
        ]);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();

        $dompdf->stream('BCC du parcours_'.$parcours->getLibelle(), ["Attachment" => true]);
    }

    #[Route('/competence/export/croise/{parcours}', name: 'app_competence_export_croise')]
    public function croise(Parcours $parcours): Response
    {
        $html = $this->renderView('pdf/bcc_export_croise.html.twig', [
            'formation' => $parcours->getFormation(),
            'parcours' => $parcours,
            'titre' => 'BCC du parcours '.$parcours->getLibelle(),
        ]);
        $dompdf = new Dompdf();
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();


        $dompdf->stream('BCC Croisé du parcours_'.$parcours->getLibelle(), ["Attachment" => true]);
    }
}
