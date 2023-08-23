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
use App\Entity\Parcours;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParcoursExportController extends AbstractController
{
    public function __construct(
        private readonly MyPDF $myPdf
    ) {
    }

    /**
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\LoaderError
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/parcours/export/{parcours}', name: 'app_parcours_export')]
    public function export(
        Parcours                $parcours,
        CalculStructureParcours $calculStructureParcours
    ): Response {
        $typeDiplome = $parcours->getFormation()?->getTypeDiplome();

        if (null === $typeDiplome) {
            throw new \Exception('Type de diplôme non trouvé');
        }

        $html = $this->renderView('pdf/parcours.html.twig', [
            'formation' => $parcours->getFormation(),
            'typeDiplome' => $typeDiplome,
            'parcours' => $parcours,
            'titre' => 'Détails du parcours '.$parcours->getLibelle(),
            'dto' => $calculStructureParcours->calcul($parcours)
        ]);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();

        $dompdf->stream('Parcours_'.$parcours->getLibelle(), ["Attachment" => true]);
    }
}
