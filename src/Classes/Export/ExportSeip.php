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
use App\Entity\CampagneCollecte;
use App\Entity\Parcours;
use App\Repository\FormationRepository;
use App\Utils\CleanTexte;
use App\Utils\Tools;
use DateTime;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;

class ExportSeip implements ExportInterface
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
        CampagneCollecte $anneeUniversitaire,
    ): void {
        $formations = $this->formationRepository->findBySearch('', $anneeUniversitaire, []);
        $this->excelWriter->nouveauFichier('Export SEIP');
        $this->excelWriter->setActiveSheetIndex(0);

        $this->excelWriter->writeCellXY(1, 1, 'Composante');
        $this->excelWriter->writeCellXY(2, 1, 'Type Diplôme');
        $this->excelWriter->writeCellXY(3, 1, 'Mention');
        $this->excelWriter->writeCellXY(4, 1, 'Parcours');
        $this->excelWriter->writeCellXY(5, 1, 'Modalités');
        $this->excelWriter->writeCellXY(6, 1, 'Stage');
        $this->excelWriter->writeCellXY(7, 1, 'Heures Stage');
        $this->excelWriter->writeCellXY(8, 1, 'Modalités Stage');
        $this->excelWriter->writeCellXY(9, 1, 'Projet');
        $this->excelWriter->writeCellXY(10, 1, 'Heures Projet');
        $this->excelWriter->writeCellXY(11, 1, 'Modalités projet');
        $this->excelWriter->writeCellXY(12, 1, 'TER/mémoire');
        $this->excelWriter->writeCellXY(14, 1, 'Modalités TER');

        $ligne = 2;
        foreach ($formations as $formation) {
            /** @var Parcours $parcours */
            foreach ($formation->getParcours() as $parcours) {
                //Composante	Type de diplôme	mention	parcours	état	remplissage	nom responsable
                $this->excelWriter->writeCellXY('A', $ligne, $formation->getComposantePorteuse()?->getLibelle());
                $this->excelWriter->writeCellXY('B', $ligne, $formation->getTypeDiplome()?->getLibelle());
                $this->excelWriter->writeCellXY('C', $ligne, $formation->getDisplay());
                if ($formation->isHasParcours()) {
                    $this->excelWriter->writeCellXY('D', $ligne, $parcours->getLibelle());
                }
                $this->excelWriter->writeCellXY(5, $ligne, $parcours->getModalitesEnseignement()?->value);
                $this->excelWriter->writeCellXY(6, $ligne, $parcours->isHasStage() ? 'Oui' : 'Non');
                $this->excelWriter->writeCellXY(7, $ligne, $parcours->getNbHeuresStages());
                $this->excelWriter->writeCellXY(8, $ligne, CleanTexte::cleanTextArea($parcours->getStageText()));
                $this->excelWriter->writeCellXY(9, $ligne, $parcours->isHasProjet() ? 'Oui' : 'Non');
                $this->excelWriter->writeCellXY(10, $ligne, $parcours->getNbHeuresProjet());
                $this->excelWriter->writeCellXY(11, $ligne, CleanTexte::cleanTextArea($parcours->getProjetText()));
                $this->excelWriter->writeCellXY(12, $ligne, $parcours->isHasMemoire()? 'Oui' : 'Non');
                $this->excelWriter->writeCellXY(13, $ligne, CleanTexte::cleanTextArea($parcours->getMemoireText()));
                $ligne++;
            }
        }


        $this->fileName = Tools::FileName('Export SEIP - ' . (new DateTime())->format('d-m-Y-H-i'), 30);
    }

    public function export(CampagneCollecte $anneeUniversitaire): StreamedResponse
    {
        $this->prepareExport($anneeUniversitaire);
        return $this->excelWriter->genereFichier($this->fileName);
    }

    public function exportLink(CampagneCollecte $anneeUniversitaire): string
    {
        $this->prepareExport($anneeUniversitaire);
        $this->excelWriter->saveFichier($this->fileName, $this->dir . 'zip/');
        return $this->fileName . '.xlsx';
    }
}
