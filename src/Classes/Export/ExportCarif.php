<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/Export/ExportSynthese.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 11/11/2023 13:07
 */

namespace App\Classes\Export;

use App\Classes\Excel\ExcelWriter;
use App\Entity\AnneeUniversitaire;
use App\Repository\FormationRepository;
use App\Utils\Tools;
use DateTime;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;

class ExportCarif
{
    private string $fileName;
    private string $dir;

    public function __construct(
        protected ExcelWriter         $excelWriter,
        KernelInterface               $kernel,
        protected FormationRepository $formationRepository,
    ) {
        $this->dir = $kernel->getProjectDir() . '/public/temp/';
    }

    private function prepareExport(
        AnneeUniversitaire $anneeUniversitaire,
    ): void {
        $formations = $this->formationRepository->findBySearch('', $anneeUniversitaire, []);
        $this->excelWriter->createFromTemplate('export_carif.xlsx');
        $this->excelWriter->setActiveSheetIndex(0);
        $ligne = 2;
        foreach ($formations as $formation) {
            foreach ($formation->getParcours() as $parcours) {
                if ($parcours->isAlternance()) {
                    //Composante	Type de diplôme	mention	parcours	état	remplissage	nom responsable
                    $this->excelWriter->writeCellXY(1, $ligne, $formation->getComposantePorteuse()?->getLibelle());
                    $this->excelWriter->writeCellXY(2, $ligne, $formation->getTypeDiplome()?->getLibelle());
                    $this->excelWriter->writeCellXY(3, $ligne, $formation->getDisplay());
                    if ($formation->isHasParcours()) {
                        $this->excelWriter->writeCellXY(4, $ligne, $parcours->getLibelle());
                    } else {
                        $this->excelWriter->writeCellXY(4, $ligne, 'Pas de parcours');
                    }

                    $this->excelWriter->writeCellXY(5, $ligne, $formation->getResponsableMention()?->getDisplay());
                    $this->excelWriter->writeCellXY(6, $ligne, $parcours->getRespParcours()?->getDisplay());
                    $this->excelWriter->writeCellXY(7, $ligne, $formation->getCodeRNCP());
                    $this->excelWriter->writeCellXY(8, $ligne, $parcours->displayRegimeInscription());
                    $this->excelWriter->writeCellXY(9, $ligne, $formation->getCodeRNCP());
                    $this->excelWriter->getColumnsAutoSize('A', 'I');
                    $ligne++;
                }
            }
        }


        $this->fileName = Tools::FileName('CARIF - ' . (new DateTime())->format('d-m-Y-H-i'), 30);
    }

    public function export(AnneeUniversitaire $annee): StreamedResponse
    {
        $this->prepareExport($annee);
        return $this->excelWriter->genereFichier($this->fileName);
    }

    public function exportLink(AnneeUniversitaire $annee): string
    {
        $this->prepareExport($annee);
        $this->excelWriter->saveFichier($this->fileName, $this->dir . 'zip/');
        return $this->fileName . '.xlsx';
    }
}
