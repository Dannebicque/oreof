<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/Export/ExportSynthese.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 11/11/2023 13:07
 */

namespace App\Classes\Export;

use App\Classes\CalculStructureParcours;
use App\Classes\Excel\ExcelWriter;
use App\Entity\AnneeUniversitaire;
use App\Repository\FormationRepository;
use App\Utils\Tools;
use DateTime;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;

class ExportCarif implements ExportInterface
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
                    $this->excelWriter->writeCellXY('A', $ligne, $formation->getComposantePorteuse()?->getLibelle());
                    $this->excelWriter->writeCellXY('B', $ligne, $formation->getTypeDiplome()?->getLibelle());
                    $this->excelWriter->writeCellXY('C', $ligne, $formation->getDisplay());
                    if ($formation->isHasParcours()) {
                        $this->excelWriter->writeCellXY('D', $ligne, $parcours->getLibelle());
                        $this->excelWriter->writeCellXY('E', $ligne, $parcours->getObjectifsParcours(), ['wrap' => true]);
                        $this->excelWriter->writeCellXY('F', $ligne, $parcours->getContenuFormation(), ['wrap' => true]);
                        $this->excelWriter->writeCellXY('G', $ligne, $parcours->getRespParcours()?->getDisplay());
                    } else {
                        $this->excelWriter->writeCellXY('E', $ligne, $formation->getObjectifsFormation(), ['wrap' => true]);
                        $this->excelWriter->writeCellXY('F', $ligne, $formation->getContenuFormation(), ['wrap' => true]);
                        $this->excelWriter->writeCellXY('G', $ligne, $formation->getResponsableMention()?->getDisplay());
                    }

//                    $calcul = new CalculStructureParcours();
//                    $dureeFormation = $calcul->calcul($parcours)->heuresEctsFormation->sommeFormationTotalPres();
//                    $dureeEntreprise = 1607 - $dureeFormation;
//                    unset($calcul);

                    $this->excelWriter->writeCellXY('K', $ligne, $parcours->getModalitesEnseignement()?->value);
                    $this->excelWriter->writeCellXY('I', $ligne, $formation->getNiveauEntree()->libelle());
                    $this->excelWriter->writeCellXY('J', $ligne, $formation->getNiveauSortie()->libelle());
                    $this->excelWriter->writeCellXY('L', $ligne, $parcours->getPrerequis(), ['wrap' => true]);
//                    $this->excelWriter->writeCellXY('N', $ligne, $dureeEntreprise);
//                    $this->excelWriter->writeCellXY('O', $ligne, $dureeFormation);
                    $this->excelWriter->writeCellXY('R', $ligne, $parcours->getLocalisation()?->getLibelle());
                   ;
//                    $this->excelWriter->getColumnsAutoSize('A', 'R');
                    $ligne++;
                }
            }
        }


        $this->fileName = Tools::FileName('CARIF - ' . (new DateTime())->format('d-m-Y-H-i'), 30);
    }

    public function export(AnneeUniversitaire $anneeUniversitaire): StreamedResponse
    {
        $this->prepareExport($anneeUniversitaire);
        return $this->excelWriter->genereFichier($this->fileName);
    }

    public function exportLink(AnneeUniversitaire $anneeUniversitaire): string
    {
        $this->prepareExport($anneeUniversitaire);
        $this->excelWriter->saveFichier($this->fileName, $this->dir . 'zip/');
        return $this->fileName . '.xlsx';
    }
}
