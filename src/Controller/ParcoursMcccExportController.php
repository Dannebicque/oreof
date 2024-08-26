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
use App\Entity\CampagneCollecte;
use App\Entity\Parcours;
use App\TypeDiplome\TypeDiplomeRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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

    #[Route('/parcours/mccc/export-version/{parcours}.{_format}', name: 'app_parcours_mccc_export_versionning')]
    public function exportMcccVersionXlsx(
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



        return match ($_format) {
            'xlsx' => $typeDiplome->exportExcelVersionMccc(
                $this->getDpe(),
                $parcours,
                 null,
                 null
            ),
            'pdf' => $typeDiplome->exportPdfMccc(
                $this->getDpe(),
                $parcours,
                null,
                 null
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

    #[Route('/parcours/mccc/export/cfvu_valid/{parcours}/{format}', name: 'app_parcours_mccc_export_cfvu_valid')]
    public function getCfvuMcccExportFromFile(
        string $format = 'complet',
        Parcours $parcours,
        EntityManagerInterface $entityManager
    ) {
        if(in_array($format, ['complet', 'simplifie']) === false){
            throw $this->createNotFoundException('File Type is invalid');
        }

        $dpe = $entityManager->getRepository(CampagneCollecte::class)->findOneBy(['defaut' => true]);

        $fileName = "MCCC-Parcours-{$parcours->getId()}-{$dpe->getAnnee()}";
        if($format === "simplifie"){
            $fileName .= "-simplifie";
        }
        $fileName .= ".pdf";

        try {
            $pdf = file_get_contents(__DIR__ . "/../../mccc-export/{$fileName}");
        }catch(\Exception $e){
            throw $this->createNotFoundException("Le fichier demandé n'a pas été trouvé");
        }

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
