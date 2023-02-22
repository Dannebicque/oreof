<?php

namespace App\Controller;

use App\Classes\MyPDF;
use App\Entity\Composante;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ComposanteExportController extends AbstractController
{
    public function __construct(private readonly MyPDF $myPdf)
    {
    }

    /**
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\LoaderError
     */
    #[Route('/composante/export/{composante}', name: 'app_composante_export')]
    public function export(Composante $composante): Response
    {
        return $this->myPdf::generePdf('pdf/composante.html.twig', ['composante' => $composante], 'dpe_composante_'.$composante->getLibelle());
    }
}
