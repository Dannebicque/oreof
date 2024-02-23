<?php

namespace App\Controller;

use App\Classes\Export\ExportCodification;
use App\Entity\Parcours;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CodificationParcoursExportController extends AbstractController
{
    #[Route('/codification/parcours/{parcours}/export', name: 'app_codification_parcours_export')]
    public function index(
        ExportCodification  $export,
        Parcours $parcours): Response
    {
        return $export->exportParcours($parcours);
    }
}
