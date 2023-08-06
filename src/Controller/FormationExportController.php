<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FormationExportController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\CalculStructureParcours;
use App\Classes\MyPDF;
use App\Entity\Formation;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormationExportController extends AbstractController
{
    public function __construct(
        private readonly MyPDF $myPdf
    )
    {
    }

    /**
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\LoaderError
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/formation/export/{slug}', name: 'app_formation_export')]
    public function export(Formation $formation,
                           CalculStructureParcours $calculStructureParcours
    ): Response
    {
        $typeDiplome = $formation->getTypeDiplome();
        $tParcours = [];
        foreach ($formation->getParcours() as $parcours) {
            $tParcours[$parcours->getId()] =  $calculStructureParcours->calcul($parcours);
        }

        $html = $this->renderView('pdf/formation.html.twig', [
            'formation' => $formation,
            'typeDiplome' => $typeDiplome,
            'tParcours' => $tParcours,
        ]);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();


        $dompdf->stream('resume', ["Attachment" => false]);


//        return $this->myPdf::generePdf('pdf/formation.html.twig', [
//            'formation' => $formation,
//            'typeDiplome' => $typeDiplome,
//        ], 'dpe_formation_'.$formation->getDisplay());
    }
}
