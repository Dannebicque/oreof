<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/ParcoursMcccExportController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 25/05/2023 14:33
 */

namespace App\Controller;

use App\Classes\GetHistorique;
use App\Entity\Parcours;
use App\TypeDiplome\TypeDiplomeRegistry;
use Symfony\Component\Routing\Annotation\Route;

class ParcoursMcccExportController extends BaseController
{
    #[Route('/parcours/mccc/export/{parcours}.{_format}', name: 'app_parcours_mccc_export')]
    public function exportMcccXlsx(
        GetHistorique $getHistorique,
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Parcours $parcours,
        string $_format = 'xlsx'
    ) {
        $formation = $parcours->getFormation();

        if (null === $formation) {
            throw new \Exception('Pas de formation.');
        }

        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome()?->getModeleMcc());

        if (null === $typeDiplome) {
            throw new \Exception('Aucun modèle MCC n\'est défini pour ce diplôme');
        }

        $cfvu = $getHistorique->getHistoriqueFormationLastStep($formation, 'cfvu');
        $conseil = $getHistorique->getHistoriqueFormationLastStep($formation, 'conseil');

        return match ($_format) {
            'xlsx' => $typeDiplome->exportExcelMccc(
                $this->getDpe(),
                $parcours,
                $cfvu?->getDate() ?? null,
                $conseil?->getDate() ?? null
            ),
            'pdf' => $typeDiplome->exportPdfMccc(
                $this->getDpe(),
                $parcours,
                $cfvu?->getDate() ?? null,
                $conseil?->getDate() ?? null
            ),
            default => throw new \Exception('Format non géré'),
        };
    }

    #[Route('/parcours/mccc/export-light/{parcours}.{_format}', name: 'app_parcours_mccc_export_light')]
    public function exportMcccLightXlsx(
        GetHistorique $getHistorique,
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Parcours $parcours,
        string $_format = 'xlsx'
    ) {
        $formation = $parcours->getFormation();

        if (null === $formation) {
            throw new \Exception('Pas de formation.');
        }

        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome()?->getModeleMcc());

        if (null === $typeDiplome) {
            throw new \Exception('Aucun modèle MCC n\'est défini pour ce diplôme');
        }

        $cfvu = $getHistorique->getHistoriqueFormationLastStep($formation, 'cfvu');
        $conseil = $getHistorique->getHistoriqueFormationLastStep($formation, 'conseil');

        return match ($_format) {
            'xlsx' => $typeDiplome->exportExcelMccc(
                $this->getDpe(),
                $parcours,
                $cfvu?->getDate() ?? null,
                $conseil?->getDate() ?? null,
                false
            ),
            'pdf' => $typeDiplome->exportPdfMccc(
                $this->getDpe(),
                $parcours,
                $cfvu?->getDate() ?? null,
                $conseil?->getDate() ?? null,
                false
            ),
            default => throw new \Exception('Format non géré'),
        };
    }
}
