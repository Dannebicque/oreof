<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/Export/LicenceMccc.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 24/05/2023 16:14
 */

namespace App\TypeDiplome\Export;

use App\Classes\Excel\ExcelWriter;
use App\DTO\TotalVolumeHeure;
use App\Entity\Parcours;
use App\Enums\RegimeInscriptionEnum;
use App\TypeDiplome\Source\LicenceTypeDiplome;

class LicenceMccc
{
    public function __construct(
        protected ExcelWriter $excelWriter
    ) {
    }


    public function exportExcelLicenceMccc(Parcours $parcours)
    {
        $formation = $parcours->getFormation();


        if (null === $formation) {
            throw new \Exception('La formation n\'existe pas');
        }
        $spreadsheet = $this->excelWriter->createFromTemplate('Annexe_MCCC.xlsx');

        // Prépare le modèle avant de dupliquer
        $modele = $spreadsheet->getSheetByName('modele');
        if ($modele === null) {
            throw new \Exception('Le modèle n\'existe pas');
        }

        //récupération des données
        // récupération des semestres du parcours puis classement par année et par ordre
        $tabSemestresAnnee = [];
        $semestres = $parcours->getSemestreParcours();
        foreach ($semestres as $semestre) {
            $tabSemestresAnnee[$semestre->getAnnee()][$semestre->getOrdreAnnee()] = $semestre;
        }

        //en-tête du fichier
        $modele->setCellValue('A3', 'Année Universitaire ' . $formation->getAnneeUniversitaire()?->getLibelle());
        $modele->setCellValue('J5', $formation->getTypeDiplome()?->getLibelle());
        $modele->setCellValue('J6', $formation->getDisplay());
        $modele->setCellValue('J7', $parcours->getLibelle());//todo: pas si parcours par défaut
        $modele->setCellValue('J11', $formation->getComposantePorteuse()?->getLibelle());
        $modele->setCellValue('J11', $parcours->getLocalisation()?->getLibelle());
        $modele->setCellValue('E21', $formation->getResponsableMention()?->getDisplay());
        $modele->setCellValue('E22', $parcours->getRespParcours()?->getDisplay());

        foreach ($parcours->getRegimeInscription() as $regimeInscription) {
            if ($regimeInscription === RegimeInscriptionEnum::FI) {
                $modele->setCellValue('C7', 'X');
            }
            if ($regimeInscription === RegimeInscriptionEnum::FC) {
                $modele->setCellValue('C9', 'X');
            }
            if ($regimeInscription === RegimeInscriptionEnum::FI_APPRENTISSAGE) {
                $modele->setCellValue('C11', 'X');
            }
            if ($regimeInscription === RegimeInscriptionEnum::FC_CONTRAT_PRO) {
                $modele->setCellValue('C13', 'X');
            }
        }
        $nbAnnees = count($tabSemestresAnnee);
        //recopie du modèle sur chaque année, puis remplissage
        for ($i = 1; $i <= $nbAnnees; $i++) {
            $clonedWorksheet = clone $modele;
            $clonedWorksheet->setTitle('Année ' . $i);
            $spreadsheet->addSheet($clonedWorksheet);
            $anneeSheets[$i] = $clonedWorksheet;

            //remplissage de chaque année
            //ligne départ 18
            $ligne = 19;
            if (array_key_exists($i, $tabSemestresAnnee)) {
                $totalAnnee = new TotalVolumeHeure();
                foreach ($tabSemestresAnnee[$i] as $key => $semestre) {
                    $this->excelWriter->setSheet($clonedWorksheet);
                    foreach ($semestre->getSemestre()->getUes() as $ue) {
                        $num = 1;

                        $nbEcs = $ue->getElementConstitutifs()->count();
                        for ($l = 0; $l < $nbEcs; $l++) {
                            $this->excelWriter->insertNewRowBefore($ligne + $l);
                        }

                        //UE
                        $this->excelWriter->writeCellXY(2, $ligne, $ue->display());
                        $this->excelWriter->writeCellXY(3, $ligne, $ue->getLibelle());
                        if ($nbEcs > 1) {
                            $this->excelWriter->mergeCellsCaR(2, $ligne, 2, $ligne + $nbEcs - 1);
                            $this->excelWriter->mergeCellsCaR(3, $ligne, 3, $ligne + $nbEcs - 1);
                        }
                        foreach ($ue->getElementConstitutifs() as $ec) {
                            $this->excelWriter->writeCellXY(1, $ligne, 'S' . $semestre->getOrdre());
                            $this->excelWriter->writeCellXY(4, $ligne, $num);//todo: gérer les cas

                            if ($ec->getFicheMatiere() !== null) {
                                //todo: plutôt un test sur le type EC
                                $this->excelWriter->writeCellXY(5, $ligne, $ec->getFicheMatiere()->getLibelle());//todo: gérer les cas
                                $this->excelWriter->writeCellXY(6, $ligne, $ec->getFicheMatiere()->getLibelleAnglais());
                                $this->excelWriter->writeCellXY(7, $ligne, $ec->getFicheMatiere()->getResponsableFicheMatiere()?->getDisplay());
                                $this->excelWriter->writeCellXY(12, $ligne, $ec->getFicheMatiere()->isEnseignementMutualise() === true ? 'O' : 'N');
                            }
                            $this->excelWriter->writeCellXY(14, $ligne, $ec->getEcts());
                            $this->excelWriter->writeCellXY(15, $ligne, $ec->getVolumeCmPresentiel());
                            $this->excelWriter->writeCellXY(16, $ligne, $ec->getVolumeTdPresentiel());
                            $this->excelWriter->writeCellXY(17, $ligne, $ec->getVolumeTpPresentiel());
                            $this->excelWriter->writeCellXY(18, $ligne, $ec->volumeTotalPresentiel());
                            $totalAnnee->addEc($ec);
                            $num++;
                            $ligne++;
                        }
                        // $ligne++;//si $num a bougé, pas de ++
                    }

                    $this->excelWriter->writeCellXY(15, $ligne, $totalAnnee->totalCmPresentiel);
                    $this->excelWriter->writeCellXY(16, $ligne, $totalAnnee->totalTdPresentiel);
                    $this->excelWriter->writeCellXY(17, $ligne, $totalAnnee->totalTpPresentiel);
                    $this->excelWriter->writeCellXY(18, $ligne, $totalAnnee->getTotalPresentiel());
                }
            }
            //suppression de la ligne modèle 18
            $this->excelWriter->removeRow(18);
        }

//        $redStyle = new Style(false, true);
//        $redStyle->getFill()
//            ->setFillType(Fill::FILL_SOLID)
//            ->getEndColor()->setARGB(Color::COLOR_RED);
//        $redStyle->getFont()->setColor(new Color(Color::COLOR_WHITE));


        //todo: supprimer la feuille de modèle

        $this->excelWriter->setSpreadsheet($spreadsheet, true);

        return $this->excelWriter->genereFichier(substr('mccc_' . $parcours->getLibelle(), 0, 30));
    }
}
