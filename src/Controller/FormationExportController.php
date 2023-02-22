<?php

namespace App\Controller;

use App\Classes\MyPDF;
use App\Entity\Formation;
use App\TypeDiplome\TypeDiplomeRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormationExportController extends AbstractController
{
    public function __construct(
        private readonly TypeDiplomeRegistry $typeDiplomeRegistry,
        private readonly MyPDF $myPdf)
    {
    }

    /**
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\LoaderError
     */
    #[Route('/formation/export/{formation}', name: 'app_formation_export')]
    public function export(Formation $formation): Response
    {
        $typeDiplome = $this->typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome());

        return $this->myPdf::generePdf('pdf/formation.html.twig', [
            'formation' => $formation,
            'typeDiplome' => $typeDiplome,
            'template' => $typeDiplome::TEMPLATE,
        ], 'dpe_formation_'.$formation->display());

    }
}
