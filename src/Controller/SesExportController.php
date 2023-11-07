<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/SesExportController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 23/08/2023 18:23
 */

namespace App\Controller;

use App\Classes\Excel\ExcelWriter;
use App\Enums\EtatDpeEnum;
use App\Repository\ComposanteRepository;
use App\Repository\FormationRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SesExportController extends BaseController
{
    #[Route('/ses/export/offre-formtion', name: 'ses_export_offre_formation')]
    public function exportOffreFormation(
        FormationRepository $formationRepository,
        ExcelWriter $excelWriter,
    ): Response {
        $formations = $formationRepository->findBySearch('', $this->getAnneeUniversitaire(), []);
        $excelWriter->createFromTemplate('export_offre_formation.xlsx');
        $excelWriter->setActiveSheetIndex(0);
        $ligne = 2;
        foreach ($formations as $formation) {
            //Composante	Type de diplôme	mention	parcours	état	remplissage	nom responsable
            $excelWriter->writeCellXY(1, $ligne, $formation->getComposantePorteuse()?->getLibelle());
            $excelWriter->writeCellXY(2, $ligne, $formation->getTypeDiplome()?->getLibelle());
            $excelWriter->writeCellXY(3, $ligne, $formation->getDisplay());
            if ($formation->isHasParcours()) {
                $excelWriter->writeCellXY(4, $ligne, $formation->getParcours()->count().' parcours');
            } else {
                $excelWriter->writeCellXY(4, $ligne, 'Pas de parcours');
            }
            $excelWriter->writeCellXY(4, $ligne, '');
            $excelWriter->writeCellXY(5, $ligne, array_key_first($formation->getEtatDpe()));
            $excelWriter->writeCellXY(6, $ligne, number_format($formation->getRemplissage()->calcul()/100, 2), [
                'pourcentage' => 'pourcentage',
            ]);
            $excelWriter->writeCellXY(7, $ligne, $formation->getResponsableMention()?->getDisplay());
            $ligne++;
            foreach ($formation->getParcours() as $parcours) {
                if ($parcours->isParcoursDefaut() === false) {
                    $excelWriter->writeCellXY(1, $ligne, $formation->getComposantePorteuse()?->getLibelle());
                    $excelWriter->writeCellXY(2, $ligne, $formation->getTypeDiplome()?->getLibelle());
                    $excelWriter->writeCellXY(3, $ligne, $formation->getDisplay());
                    $excelWriter->writeCellXY(4, $ligne, $parcours->getLibelle());
                    $excelWriter->writeCellXY(5, $ligne, array_key_first($parcours->getEtatParcours()));
                    $excelWriter->writeCellXY(6, $ligne, number_format($parcours->getRemplissage()->calcul()/100, 2), [
                        'pourcentage' => 'pourcentage',
                    ]);
                    $excelWriter->writeCellXY(7, $ligne, $parcours->getRespParcours()?->getDisplay());
                    $ligne++;
                }
            }
        }
        //MCCC -2023-2024 -  M Psychologie sociale, du travail et des organisations
        $fileName = substr('OF - ' . (new DateTime())->format('d-m-Y-h-i'), 0, 30);
        return  $excelWriter->genereFichier($fileName);
    }
}
