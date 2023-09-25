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

        if (null === $formation) {
            throw new \Exception('Pas de formation.');
        }

        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome()?->getModeleMcc());

        if (null === $typeDiplome) {
            throw new \Exception('Aucun modèle MCC n\'est défini pour ce diplôme');
        }

        return match ($_format) {
            'xlsx' => $typeDiplome->exportExcelMccc(
                $this->getAnneeUniversitaire(),
                $parcours
            ),
            'pdf' => $typeDiplome->exportPdfMccc(
                $this->getAnneeUniversitaire(),
                $parcours
            ),
            default => throw new \Exception('Format non géré'),
        };
    }

    #[Route('/parcours/mccc/export-light/{parcours}.{_format}', name: 'app_parcours_mccc_export_light')]
    public function exportMcccLightXlsx(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Parcours $parcours,
        string $_format = 'xlsx'
    )
    {
        $formation = $parcours->getFormation();

        if (null === $formation) {
            throw new \Exception('Pas de formation.');
        }

        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome()?->getModeleMcc());

        if (null === $typeDiplome) {
            throw new \Exception('Aucun modèle MCC n\'est défini pour ce diplôme');
        }

        return match ($_format) {
            'xlsx' => $typeDiplome->exportExcelMccc(
                $this->getAnneeUniversitaire(),
                $parcours,
                null,
                false
            ),
            'pdf' => $typeDiplome->exportPdfMccc(
                $this->getAnneeUniversitaire(),
                $parcours,
                null,
                false
            ),
            default => throw new \Exception('Format non géré'),
        };
    }
}
