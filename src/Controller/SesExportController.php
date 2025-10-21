<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/SesExportController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 23/08/2023 18:23
 */

namespace App\Controller;

use App\Classes\Export\ExportSynthese;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SesExportController extends BaseController
{
    #[Route('/ses/export/offre-formtion', name: 'ses_export_offre_formation')]
    public function exportOffreFormation(
        ExportSynthese $exportSynthese,
    ): Response {
        return $exportSynthese->export($this->getCampagneCollecte());
    }

    #[Route('/ses/export/offre-formtion-brut', name: 'ses_export_offre_formation_brut')]
    public function exportOffreFormationBrut(
        ExportSynthese $exportSynthese,
    ): Response {
        return $exportSynthese->exportBrut($this->getCampagneCollecte());
    }
}
