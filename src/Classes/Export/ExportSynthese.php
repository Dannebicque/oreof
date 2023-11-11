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

class ExportSynthese
{
    private string $fileName;
    private string $dir;
    public function __construct(
        protected ExcelWriter         $excelWriter,
        KernelInterface                   $kernel,
        protected FormationRepository $formationRepository,
    ) {
        $this->dir = $kernel->getProjectDir() . '/public/temp/';
    }

    private function prepareExport(
        AnneeUniversitaire $anneeUniversitaire,
    ): void {
        $formations = $this->formationRepository->findBySearch('', $anneeUniversitaire, []);
        $this->excelWriter->createFromTemplate('export_offre_formation.xlsx');
        $this->excelWriter->setActiveSheetIndex(0);
        $ligne = 2;
        foreach ($formations as $formation) {
            //Composante	Type de diplôme	mention	parcours	état	remplissage	nom responsable
            $this->excelWriter->writeCellXY(1, $ligne, $formation->getComposantePorteuse()?->getLibelle());
            $this->excelWriter->writeCellXY(2, $ligne, $formation->getTypeDiplome()?->getLibelle());
            $this->excelWriter->writeCellXY(3, $ligne, $formation->getDisplay());
            if ($formation->isHasParcours()) {
                $this->excelWriter->writeCellXY(4, $ligne, $formation->getParcours()->count() . ' parcours');
            } else {
                $this->excelWriter->writeCellXY(4, $ligne, 'Pas de parcours');
            }
            $this->excelWriter->writeCellXY(4, $ligne, '');
            $this->excelWriter->writeCellXY(5, $ligne, array_key_first($formation->getEtatDpe()));
            $this->excelWriter->writeCellXY(6, $ligne, number_format($formation->getRemplissage()->calcul() / 100, 2), [
                'pourcentage' => 'pourcentage',
            ]);
            $this->excelWriter->writeCellXY(7, $ligne, $formation->getResponsableMention()?->getDisplay());
            $ligne++;
            foreach ($formation->getParcours() as $parcours) {
                if ($parcours->isParcoursDefaut() === false) {
                    $this->excelWriter->writeCellXY(1, $ligne, $formation->getComposantePorteuse()?->getLibelle());
                    $this->excelWriter->writeCellXY(2, $ligne, $formation->getTypeDiplome()?->getLibelle());
                    $this->excelWriter->writeCellXY(3, $ligne, $formation->getDisplay());
                    $this->excelWriter->writeCellXY(4, $ligne, $parcours->getLibelle());
                    $this->excelWriter->writeCellXY(5, $ligne, array_key_first($parcours->getEtatParcours()));
                    $this->excelWriter->writeCellXY(6, $ligne, number_format($parcours->getRemplissage()->calcul() / 100, 2), [
                        'pourcentage' => 'pourcentage',
                    ]);
                    $this->excelWriter->writeCellXY(7, $ligne, $parcours->getRespParcours()?->getDisplay());
                    $ligne++;
                }
            }
        }
        $this->fileName = Tools::FileName('OF - ' . (new DateTime())->format('d-m-Y-H-i'), 30);
    }

    public function export(AnneeUniversitaire $annee): StreamedResponse
    {
        $this->prepareExport($annee);
        return $this->excelWriter->genereFichier($this->fileName);
    }

    public function exportLink(AnneeUniversitaire $annee): string
    {
        $this->prepareExport($annee);
        $this->excelWriter->saveFichier($this->fileName, $this->dir.'zip/');
        return $this->fileName . '.xlsx';
    }
}
