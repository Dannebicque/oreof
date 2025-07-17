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
use App\Classes\MyGotenbergPdf;
use App\Entity\Formation;
use App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class FormationExportController extends AbstractController
{
    public function __construct(
        private readonly MyGotenbergPdf $myPdf
    )
    {
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws TypeDiplomeNotFoundException
     */
    #[Route('/formation/export/{slug}', name: 'app_formation_export')]
    public function export(
        Formation $formation,
        CalculStructureParcours $calculStructureParcours
    ): Response
    {
        $typeDiplome = $formation->getTypeDiplome();
        $tParcours = [];
        foreach ($formation->getParcours() as $parcours) {
            $tParcours[$parcours->getId()] =  $calculStructureParcours->calcul($parcours);
        }

        return $this->myPdf->render('pdf/formation.html.twig', [
            'formation' => $formation,
            'typeDiplome' => $typeDiplome,
            'titre' => 'Détails de la formation '.$formation->getDisplay(),
            'tParcours' => $tParcours,
        ], 'Formation_'.$formation->getDisplay().'.pdf');
    }
}
