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
use App\Classes\MyDomPdf;
use App\Classes\MyPDF;
use App\Entity\Parcours;
use Dompdf\Dompdf;
use Dompdf\Options;
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
        MyDomPdf $myDomPdf,
        Parcours                $parcours,
        CalculStructureParcours $calculStructureParcours
    ): Response {
        $typeDiplome = $parcours->getFormation()?->getTypeDiplome();

        if (null === $typeDiplome) {
            throw new \Exception('Type de diplôme non trouvé');
        }
        return $myDomPdf->render('pdf/parcours.html.twig', [
            'formation' => $parcours->getFormation(),
            'typeDiplome' => $typeDiplome,
            'parcours' => $parcours,
            'hasParcours' => $parcours->getFormation()->isHasParcours(),
            'titre' => 'Détails du parcours '.$parcours->getLibelle(),
            'dto' => $calculStructureParcours->calcul($parcours)
        ], 'Parcours_'.$parcours->getLibelle());
    }
}
