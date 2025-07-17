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
use App\Classes\GetDpeParcours;
use App\Classes\GetHistorique;
use App\Entity\CampagneCollecte;
use App\Enums\RegimeInscriptionEnum;
use App\Repository\FormationRepository;
use App\Service\ProjectDirProvider;
use App\Utils\Tools;
use DateTime;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;

class ExportRegime implements ExportInterface
{
    private string $fileName;
    private string $dir;

    public function __construct(
        protected GetHistorique       $getHistorique,
        protected ExcelWriter         $excelWriter,
        ProjectDirProvider            $projectDirProvider,
        protected FormationRepository $formationRepository,
    ) {
        $this->dir = $projectDirProvider->getProjectDir() . '/public/temp/';
    }

    private function prepareExport(
        CampagneCollecte $anneeUniversitaire,
    ): void {
        $formations = $this->formationRepository->findBySearch('', $anneeUniversitaire);
        $this->excelWriter->nouveauFichier('Export Régimes');
        $this->excelWriter->setActiveSheetIndex(0);

        $this->excelWriter->writeCellXY(1, 1, 'Composante');
        $this->excelWriter->writeCellXY(2, 1, 'Type Diplôme');
        $this->excelWriter->writeCellXY(3, 1, 'Mention');
        $this->excelWriter->writeCellXY(4, 1, 'Parcours');
        $this->excelWriter->writeCellXY(5, 1, 'Lieu de formation');
        $this->excelWriter->writeCellXY(6, 1, 'Resp. Mention');
        $this->excelWriter->writeCellXY(7, 1, 'Co. Resp. Mention');
        $this->excelWriter->writeCellXY(8, 1, 'Resp. Parcours');
        $this->excelWriter->writeCellXY(9, 1, 'Co. Resp. Parcours');
        $this->excelWriter->writeCellXY(10, 1, 'RNCP');
        $this->excelWriter->writeCellXY(11, 1, 'Validation CFVU');
        $i = 0;
        foreach (RegimeInscriptionEnum::cases() as $regime) {
            $this->excelWriter->writeCellXY(12 + $i, 1, $regime->value);
            $i++;
        }

        $ligne = 2;
        foreach ($formations as $formation) {
            foreach ($formation->getParcours() as $parcours) {
                $this->excelWriter->writeCellXY(1, $ligne, $formation->getComposantePorteuse()?->getLibelle());
                $this->excelWriter->writeCellXY(2, $ligne, $formation->getTypeDiplome()?->getLibelle());
                $this->excelWriter->writeCellXY(3, $ligne, $formation->getDisplay());
                if ($formation->isHasParcours()) {
                    $this->excelWriter->writeCellXY(4, $ligne, $parcours->getLibelle());
                    $this->excelWriter->writeCellXY(5, $ligne, $parcours->getLocalisation()?->getLibelle());
                } else {
                    $this->excelWriter->writeCellXY(4, $ligne, 'Pas de parcours');
                    $texte = '';
                    foreach ($formation->getLocalisationMention() as $localisation) {
                        $texte .= $localisation->getLibelle() . ', ';
                    }
                    $this->excelWriter->writeCellXY(5, $ligne, substr($texte, 0, -2));
                }
                $dpeParcours = GetDpeParcours::getFromParcours($parcours);
                $this->excelWriter->writeCellXY(6, $ligne, $formation->getResponsableMention()?->getDisplay());
                $this->excelWriter->writeCellXY(7, $ligne, $formation->getCoResponsable()?->getDisplay());
                $this->excelWriter->writeCellXY(8, $ligne, $parcours->getRespParcours()?->getDisplay());
                $this->excelWriter->writeCellXY(9, $ligne, $parcours->getCoResponsable()?->getDisplay());
                $this->excelWriter->writeCellXY(10, $ligne, $formation->getCodeRNCP());
                $this->excelWriter->writeCellXY(11, $ligne, $this->getHistorique->getHistoriqueParcoursLastStep($dpeParcours, 'cfvu')?->getDate()?->format('d/m/Y') ?? 'Non validé');
                $i = 0;
                foreach (RegimeInscriptionEnum::cases() as $regime) {
                    if (in_array($regime, $parcours->getRegimeInscription())) {
                        $this->excelWriter->writeCellXY(12 + $i, $ligne, 'X', [
                            'style' => 'HORIZONTAL_CENTER'
                        ]);
                    }
                    $i++;
                }

                $this->excelWriter->getColumnsAutoSize('A', 'M');
                $ligne++;
            }
        }

        $this->fileName = Tools::FileName('OF - REGIME - ' . (new DateTime())->format('d-m-Y-H-i'), 30);
    }

    public function export(CampagneCollecte $anneeUniversitaire): StreamedResponse
    {
        $this->prepareExport($anneeUniversitaire);
        return $this->excelWriter->genereFichier($this->fileName);
    }

    public function exportLink(CampagneCollecte $campagneCollecte): string
    {
        $this->prepareExport($campagneCollecte);
        $this->excelWriter->saveFichier($this->fileName, $this->dir . 'zip/');
        return $this->fileName . '.xlsx';
    }
}
