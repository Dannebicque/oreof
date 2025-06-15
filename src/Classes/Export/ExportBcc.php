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
use App\Classes\GetElementConstitutif;
use App\Entity\CampagneCollecte;
use App\Entity\Parcours;
use App\Repository\FormationRepository;
use App\Utils\Tools;
use DateTime;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;

class ExportBcc implements ExportInterface
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
        Parcours $parcours
    ): void {
        $formation = $parcours->getFormation();
        $this->excelWriter->nouveauFichier('Export BCC');
        $this->excelWriter->setActiveSheetIndex(0);
        $col = 3;

        $this->excelWriter->writeCellName('C1', 'Type Diplôme', [
            'style' => 'HORIZONTAL_RIGHT',
        ]);
        $this->excelWriter->writeCellName('D1', $formation->getTypeDiplome()?->getLibelle());
        $this->excelWriter->writeCellName('C2', 'Formation', [
            'style' => 'HORIZONTAL_RIGHT',
        ]);
        $this->excelWriter->writeCellName('D2', $formation->getDisplay());
        $this->excelWriter->writeCellName('C3', 'Parcours', [
            'style' => 'HORIZONTAL_RIGHT',
        ]);
        $this->excelWriter->writeCellName('D3', $parcours->isParcoursDefaut() === false ? $parcours->getDisplay() : '');
        $this->excelWriter->writeCellName('C4', 'Composante', [
            'style' => 'HORIZONTAL_RIGHT',
        ]);
        $this->excelWriter->writeCellName('D4', $formation->getComposantePorteuse()?->getLibelle());
        $this->excelWriter->writeCellName('C5', 'Localisation', [
            'style' => 'HORIZONTAL_RIGHT',
        ]);
        $this->excelWriter->writeCellName('D5', $parcours->getLocalisation()?->getLibelle());
        $this->excelWriter->getColumnDimension('A', 30);
        $this->excelWriter->getColumnDimension('B', 50);

        foreach ($parcours->getSemestreParcours() as $semParc) {
            $ligne = 7;
            $debutCol = $col;
            if ($semParc->getSemestre() !== null && !$semParc->getSemestre()->isNonDispense()) {
                if ($semParc->getSemestre()->getSemestreRaccroche() !== null) {
                    $semestre = $semParc->getSemestre()->getSemestreRaccroche();
                } else {
                    $semestre = $semParc->getSemestre();
                }
                $nbCols = 0;

                foreach ($semestre->getUes() as $ue) {
                    if ($ue->getUeParent() === null) {
                        if ($ue->getUeEnfants()->count() == 0) {
                            $nbCols += $ue->getElementConstitutifs()->count();
                        } else {
                            foreach ($ue->getUeEnfants() as $uee) {
                                $nbCols += $uee->getElementConstitutifs()->count();
                            }
                        }
                    }
                }
                $this->excelWriter->writeCellXY($col, $ligne, 'Semestre ' . $semParc->display(), [
                    'style' => 'HORIZONTAL_CENTER',
                ]);
                $this->excelWriter->mergeCellsCaR($col, $ligne, $col + $nbCols - 1, $ligne);
                $ligne++;
                $col = $debutCol;

                foreach ($semestre->getUes() as $ue) {
                    if ($ue->getUeParent() === null) {
                        if ($ue->getUeEnfants()->count() == 0) {
                            $this->excelWriter->writeCellXY($col, $ligne, $ue->display($parcours), [
                                'style' => 'HORIZONTAL_CENTER',
                            ]);
                            $this->excelWriter->mergeCellsCaR($col, $ligne, $col + $ue->getElementConstitutifs()->count() - 1, $ligne);
                            $col += $ue->getElementConstitutifs()->count();
                        } else {
                            foreach ($ue->getUeEnfants() as $uee) {
                                $this->excelWriter->writeCellXY($col, $ligne, $uee->display($parcours), [
                                    'style' => 'HORIZONTAL_CENTER',
                                ]);
                                $this->excelWriter->mergeCellsCaR($col, $ligne, $col + $uee->getElementConstitutifs()->count() - 1, $ligne);
                                $col += $uee->getElementConstitutifs()->count();
                            }
                        }
                    }
                }

                $ligne++;
                $col = $debutCol;
                foreach ($semestre->getUes() as $ue) {
                    if ($ue->getUeParent() === null) {
                        if ($ue->getUeEnfants()->count() == 0) {
                            foreach ($ue->getElementConstitutifs() as $ec) {
                                $this->excelWriter->writeCellXY($col, $ligne, $ec->getCode(), [
                                    'style' => 'HORIZONTAL_CENTER',
                                ]);
                                $col++;
                            }
                        } else {
                            foreach ($ue->getUeEnfants() as $uee) {
                                foreach ($uee->getElementConstitutifs() as $ec) {
                                    $this->excelWriter->writeCellXY($col, $ligne, $ec->getCode(), [
                                        'style' => 'HORIZONTAL_CENTER',
                                    ]);
                                    $col++;
                                }
                            }
                        }
                    }
                }

                $ligne++;

                foreach ($parcours->getBlocCompetences() as $bcc) {
                    foreach ($bcc->getCompetences() as $competence) {
                        $this->excelWriter->writeCellXY(1, $ligne, $bcc->display(), ['wrap' => true]);
                        $this->excelWriter->writeCellXY(2, $ligne, $competence->display(), ['wrap' => true]);
                        $col = $debutCol;

                        foreach ($semestre->getUes() as $ue) {
                            if ($ue->getUeParent() === null) {
                                if ($ue->getUeEnfants()->count() == 0) {
                                    foreach ($ue->getElementConstitutifs() as $ec) {
                                        $raccroche = $ec->getFicheMatiere()?->getParcours() !== $parcours;
                                        if ($raccroche) {
                                            $getElement = new GetElementConstitutif($ec, $parcours);
                                            $getElement->setIsRaccroche($raccroche);
                                            $competences = $getElement->getBccs();
                                        } else {
                                            $competences = $ec->getFicheMatiere()?->getCompetences();
                                        }
                                        $this->excelWriter->writeCellXY($col, $ligne, $competences?->contains($competence) ? 'X' : '', [
                                            'style' => 'HORIZONTAL_CENTER',
                                            'valign' => 'VERTICAL_CENTER'
                                        ]);
                                        $col++;
                                    }
                                } else {
                                    foreach ($ue->getUeEnfants() as $uee) {
                                        foreach ($uee->getElementConstitutifs() as $ec) {
                                            $this->excelWriter->writeCellXY($col, $ligne, $competence->getFicheMatieres()->contains($ec->getFicheMatiere()) ? 'X' : '', [
                                                'style' => 'HORIZONTAL_CENTER',
                                                'valign' => 'VERTICAL_CENTER'
                                            ]);
                                            $col++;
                                        }
                                    }
                                }
                            }
                        }
                        $ligne++;
                    }
                }
            }
        }

        $this->excelWriter->borderOutsiteInside(1, 7, $col-1, $ligne-1);
        $formation = $parcours->getFormation();
        if ($parcours->isParcoursDefaut() === false) {
            $texte = $formation->gettypeDiplome()?->getLibelleCourt(). ' ' . $formation->getSigle() . ' ' . $parcours->getSigle();
        } else {
            $texte = $formation->gettypeDiplome()?->getLibelleCourt() . ' ' . $formation->getSigle();
        }

        $this->fileName = Tools::FileName('BCC Croisé Global - ' . $texte . ' ' . (new DateTime())->format('d-m-Y-H-i'), 30);
    }

    public function export(Parcours $parcours): StreamedResponse
    {
        $this->prepareExport($parcours);
        return $this->excelWriter->genereFichier($this->fileName);
    }

    public function exportLink(CampagneCollecte $campagneCollecte): string
    {
        // TODO: Implement exportLink() method.
    }
}
