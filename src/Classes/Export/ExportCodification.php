<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/Export/ExportCodification.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 18/01/2024 17:23
 */

namespace App\Classes\Export;

use App\Classes\Excel\ExcelWriter;
use App\Utils\Tools;
use DateTime;

class ExportCodification
{
    public function __construct(protected ExcelWriter         $excelWriter)
    {
    }

    public function exportFormations(array $formations)
    {
        $this->excelWriter->nouveauFichier('Export Codification');
        $this->excelWriter->setActiveSheetIndex(0);

        $this->excelWriter->writeCellXY(1, 1, 'Composante');
        $this->excelWriter->writeCellXY(2, 1, 'Mention');
        $this->excelWriter->writeCellXY(3, 1, 'Parcours');
        $this->excelWriter->writeCellXY(4, 1, 'Ville');
        $this->excelWriter->writeCellXY(5, 1, 'Code Diplôme');
        $this->excelWriter->writeCellXY(6, 1, 'Version Diplôme');
        $this->excelWriter->writeCellXY(7, 1, 'Année d\'étude');
        $this->excelWriter->writeCellXY(8, 1, 'Code Etape');
        $this->excelWriter->writeCellXY(9, 1, 'Version Etape');

        $ligne = 2;


        foreach ($formations as $formation) {
            if ($formation->isHasParcours()) {
                foreach ($formation->getParcours() as $parcours) {
                    if (null !== $parcours) {
                        foreach ($parcours->getAnnees() as $annee) {
                            $this->excelWriter->writeCellXY(1, $ligne, $formation->getComposantePorteuse()?->getLibelle());
                            $this->excelWriter->writeCellXY(2, $ligne, $formation->getDisplayLong());
                            $this->excelWriter->writeCellXY(3, $ligne, $parcours->getLibelle());
                            $this->excelWriter->writeCellXY(4, $ligne, $parcours->getLocalisation()?->getLibelle());
                            $this->excelWriter->writeCellXY(5, $ligne, $parcours->getCodeDiplome($annee));
                            $this->excelWriter->writeCellXY(6, $ligne, $parcours->getCodeVersionDiplome($annee));
                            $this->excelWriter->writeCellXY(7, $ligne, 'Année ' . $annee);
                            $this->excelWriter->writeCellXY(8, $ligne, $parcours->getCodeEtape($annee));
                            $this->excelWriter->writeCellXY(9, $ligne, $parcours->getCodeVersionEtape($annee));
                            $ligne++;
                        }
                    }
                }
            } else {
                $parcours = $formation->defaultParcours();
                if (null !== $parcours) {
                    foreach ($parcours->getAnnees() as $annee) {
                        $this->excelWriter->writeCellXY(1, $ligne, $formation->getComposantePorteuse()?->getLibelle());
                        $this->excelWriter->writeCellXY(2, $ligne, $formation->getDisplayLong());
                        $this->excelWriter->writeCellXY(3, $ligne, $parcours->getLibelle());
                        $this->excelWriter->writeCellXY(4, $ligne, $parcours->getLocalisation()?->getLibelle());
                        $this->excelWriter->writeCellXY(5, $ligne, $parcours->getCodeDiplome($annee));
                        $this->excelWriter->writeCellXY(6, $ligne, $parcours->getCodeVersionDiplome($annee));
                        $this->excelWriter->writeCellXY(7, $ligne, 'Année ' . $annee);
                        $this->excelWriter->writeCellXY(8, $ligne, $parcours->getCodeEtape($annee));
                        $this->excelWriter->writeCellXY(9, $ligne, $parcours->getCodeVersionEtape($annee));
                        $ligne++;
                    }
                }
            }
        }

        $this->excelWriter->getColumnsAutoSize('A', 'M');



        $fileName = Tools::FileName('Codification-OReOF' . (new DateTime())->format('d-m-Y-H-i'), 30);
        return $this->excelWriter->genereFichier($fileName, true);
    }
}
