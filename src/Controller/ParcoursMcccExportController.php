<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/ParcoursMcccExportController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 25/05/2023 14:33
 */

namespace App\Controller;

use App\Entity\Parcours;
use App\TypeDiplome\TypeDiplomeRegistry;
use Symfony\Component\Routing\Annotation\Route;

class ParcoursMcccExportController extends BaseController
{
    #[Route('/parcours/mccc/export/{parcours}.{_format}', name: 'app_parcours_mccc_export')]
    public function exportMcccXlsx(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Parcours $parcours,
        string $_format = 'xlsx'
    )
    {
        $formation = $parcours->getFormation();
        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome()->getModeleMcc());
        return $typeDiplome->exportExcelMccc($parcours);
    }
}
