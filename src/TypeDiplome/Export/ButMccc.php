<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/Export/butMcccMccc.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 24/05/2023 16:14
 */

namespace App\TypeDiplome\Export;

use App\Classes\Excel\ExcelWriter;
use App\DTO\TotalVolumeHeure;
use App\Entity\AnneeUniversitaire;
use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\Formation;
use App\Entity\Mccc;
use App\Entity\Parcours;
use App\Enums\RegimeInscriptionEnum;
use App\Repository\FicheMatiereRepository;
use App\Repository\TypeEpreuveRepository;
use App\TypeDiplome\Source\ButTypeDiplome;
use App\Utils\Tools;
use DateTimeInterface;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ButMccc
{
    //todo: ajouter un watermark sur le doc ou une mention que la mention est définitive ou pas.
    //todo: gérer la date de vote

    // Pages
    public const PAGE_MODELE = 'modele';
    public const PAGE_REF_COMPETENCES = 'ref. compétences';
    // Cellules
    public const CEL_DOMAINE = 'K1';
    public const CEL_INTITULE_FORMATION = 'K4';
    public const CEL_INTITULE_PARCOURS = 'K5';
    public const CEL_SEMESTRE_ETUDE = 'K7';
    public const CEL_COMPOSANTE = 'K2';
    public const CEL_SITE_FORMATION = 'K3';
    public const CEL_ANNEE_UNIVERSITAIRE = 'K6';
    public const CEL_REGIME_FI = 'E9';
    public const CEL_REGIME_FC = 'E11';
    public const CEL_REGIME_FI_APPRENTISSAGE = 'E13';
    public const CEL_REGIME_FC_CONTRAT_PRO = 'E15';

    //Colonnes sur Modèles

    public const COL_CODE_ELEMENT = 1;
    public const COL_CODE_EC = 2;
    public const COL_INTITULE = 3;
    public const COL_VOL_ETUDIANT = 4;
    public const COL_CM = 5;
    public const COL_TD = 6;
    public const COL_TP = 7;
    public const COL_HEURE_AUTONOMIE = 8;
    private bool $versionFull = true;
    private string $fileName;
    private Parcours $parcours;

    public function __construct(
        protected FicheMatiereRepository $ficheMatiereRepository,
        protected ExcelWriter            $excelWriter,
    ) {
    }


    /**
     * @throws Exception
     * @throws \Exception
     */
    public function genereExcelbutMccc(
        AnneeUniversitaire $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface $dateEdition = null,
        bool               $versionFull = true
    ): void {
        $tabColonnes = [
            'td_tp_oral' => ['pourcentage' => 'L', 'nombre' => 'M'],
            'td_tp_ecrit' => ['pourcentage' => 'N', 'nombre' => 'O'],
            'td_tp_rapport' => ['pourcentage' => 'P', 'nombre' => 'Q'],
            'td_tp_autre' => ['pourcentage' => 'R', 'nombre' => 'S'],
            'cm_ecrit' => ['pourcentage' => 'T', 'nombre' => 'U'],
            'cm_rapport' => ['pourcentage' => 'V', 'nombre' => 'W'],
            'iut_portfolio' => ['pourcentage' => 'X', 'nombre' => 'Y'],
            'iut_livrable' => ['pourcentage' => 'Z', 'nombre' => 'AA'],
            'iut_rapport' => ['pourcentage' => 'AB', 'nombre' => 'AC'],
            'iut_soutenance' => ['pourcentage' => 'AD', 'nombre' => 'AE'],
            'hors_iut_entreprise' => ['pourcentage' => 'AF', 'nombre' => 'AG'],
            'hors_iut_rapport' => ['pourcentage' => 'AH', 'nombre' => 'AI'],
            'hors_iut_soutenance' => ['pourcentage' => 'AJ', 'nombre' => 'AK'],
        ];

        $totalCm = 0;
        $totalTd = 0;
        $totalTp = 0;
        $totalTe = 0;

        //todo: gérer la date de publication et un "marquage" sur le document si pré-CFVU
        $this->versionFull = $versionFull;
        $formation = $parcours->getFormation();
        $this->parcours = $parcours;

        if (null === $formation) {
            throw new \Exception('La formation n\'existe pas');
        }

        $spreadsheet = $this->excelWriter->createFromTemplate('Annexe_MCCC_BUT.xlsx');

        // Prépare le modèle avant de dupliquer
        $modele = $spreadsheet->getSheetByName(self::PAGE_MODELE);
        if ($modele === null) {
            throw new \Exception('Le modèle n\'existe pas');
        }

        //récupération des données
        // récupération des semestres du parcours puis classement par année et par ordre
        $tabSemestres = [];
        $semestres = $parcours->getSemestreParcours();
        foreach ($semestres as $semParc) {
            if ($semParc->getSemestre()?->getSemestreRaccroche() !== null) {
                $tabSemestres[$semParc->getOrdre()] = $semParc->getSemestre()?->getSemestreRaccroche();
            } else {
                $tabSemestres[$semParc->getOrdre()] = $semParc;
            }
        }

        //en-tête du fichier
        $modele->setCellValue(self::CEL_DOMAINE, $formation->getDomaine()?->getLibelle());
        $modele->setCellValue(self::CEL_COMPOSANTE, $formation->getComposantePorteuse()?->getLibelle());
        $modele->setCellValue(self::CEL_INTITULE_FORMATION, $formation->getDisplay());
        $modele->setCellValue(self::CEL_INTITULE_PARCOURS, $parcours->isParcoursDefaut() === false ? $parcours->getLibelle() : '');
        if ($formation->isHasParcours() === false) {
            $modele->setCellValue(self::CEL_SITE_FORMATION, $formation->getLocalisationMention()[0]?->getLibelle());
        } else {
            $modele->setCellValue(self::CEL_SITE_FORMATION, $parcours->getLocalisation()?->getLibelle());
        }

        // fiches
        $tabFichesRessources = [];
        $fiches = $this->ficheMatiereRepository->findByParcours($parcours);
        foreach ($fiches as $fiche) {
            if ($fiche->getTypeMatiere() === FicheMatiere::TYPE_MATIERE_RESSOURCE) {
                $tabFichesRessources[$fiche->getSemestre()][$fiche->getSigle()] = $fiche;
            }
        }

        $tabFichesSaes = [];
        foreach ($fiches as $fiche) {
            if ($fiche->getTypeMatiere() === FicheMatiere::TYPE_MATIERE_SAE) {
                $tabFichesSaes[$fiche->getSemestre()][$fiche->getSigle()] = $fiche;
            }
        }

        foreach ($parcours->getRegimeInscription() as $regimeInscription) {
            if ($regimeInscription === RegimeInscriptionEnum::FI) {
                $modele->setCellValue(self::CEL_REGIME_FI, 'X');
            }
            if ($regimeInscription === RegimeInscriptionEnum::FC) {
                $modele->setCellValue(self::CEL_REGIME_FC, 'X');
            }
            if ($regimeInscription === RegimeInscriptionEnum::FI_APPRENTISSAGE) {
                $modele->setCellValue(self::CEL_REGIME_FI_APPRENTISSAGE, 'X');
            }
            if ($regimeInscription === RegimeInscriptionEnum::FC_CONTRAT_PRO) {
                $modele->setCellValue(self::CEL_REGIME_FC_CONTRAT_PRO, 'X');
            }
        }

        $index = 1;

        //recopie du modèle sur chaque année, puis remplissage
        foreach ($tabSemestres as $i => $semestres) {
            $clonedWorksheet = clone $modele;
            $clonedWorksheet->setTitle('Semestre S' . $i);
            $spreadsheet->addSheet($clonedWorksheet, $index);
            $index++;
            $semestreSheets[$i] = $clonedWorksheet;

            //remplissage de chaque année
            //ligne départ 18
            $ligne = 24;
            if (array_key_exists($i, $tabSemestres)) {
                $totalAnnee = new TotalVolumeHeure();
                $this->excelWriter->setSheet($clonedWorksheet);
                $this->excelWriter->writeCellName(self::CEL_SEMESTRE_ETUDE, 'Semestre S' . $i);

                ksort($tabFichesRessources[$i]);
                ksort($tabFichesSaes[$i]);

                foreach ($tabFichesRessources[$i] as $fiche) {
                    $this->excelWriter->insertNewRowBefore($ligne);
                    $this->excelWriter->writeCellXY(self::COL_CODE_ELEMENT, $ligne, '', ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_CODE_EC, $ligne, $fiche->getSigle(), ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_INTITULE, $ligne, $fiche->getLibelle(), ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_VOL_ETUDIANT, $ligne, $fiche->getVolumeEtudiant(), ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_CM, $ligne, $fiche->getVolumeCmPresentiel() === 0 ? '' : $fiche->getVolumeCmPresentiel(), ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_TD, $ligne, $fiche->getVolumeTdPresentiel() === 0 ? '' : $fiche->getVolumeTdPresentiel(), ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_TP, $ligne, $fiche->getVolumeTpPresentiel() === 0 ? '' : $fiche->getVolumeTpPresentiel(), ['style' => 'HORIZONTAL_CENTER']);

                    $totalCm += $fiche->getVolumeCmPresentiel();
                    $totalTd += $fiche->getVolumeTdPresentiel();
                    $totalTp += $fiche->getVolumeTpPresentiel();

                    //MCCC
                    $this->writeMccc($fiche, $tabColonnes, $ligne);


                    $ligne++;
                }

                $finRessource = $ligne - 1;

                $this->excelWriter->insertNewRowBefore($ligne);
                $ligne++;
                $debutSae = $ligne;

                foreach ($tabFichesSaes[$i] as $fiche) {
                    $this->excelWriter->insertNewRowBefore($ligne);
                    $this->excelWriter->writeCellXY(self::COL_CODE_ELEMENT, $ligne, '', ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_CODE_EC, $ligne, $fiche->getSigle(), ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_INTITULE, $ligne, $fiche->getLibelle(), ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_VOL_ETUDIANT, $ligne, $fiche->getVolumeEtudiant(), ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_CM, $ligne, $fiche->getVolumeCmPresentiel() === 0.0 ? '' : $fiche->getVolumeCmPresentiel(), ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_TD, $ligne, $fiche->getVolumeTdPresentiel() === 0.0 ? '' : $fiche->getVolumeTdPresentiel(), ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_TP, $ligne, $fiche->getVolumeTpPresentiel() === 0.0 ? '' : $fiche->getVolumeTpPresentiel(), ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_HEURE_AUTONOMIE, $ligne, $fiche->getVolumeTe());

                    $totalCm += $fiche->getVolumeCmPresentiel();
                    $totalTd += $fiche->getVolumeTdPresentiel();
                    $totalTp += $fiche->getVolumeTpPresentiel();
                    $totalTe += $fiche->getVolumeTe();

                    //MCCC
                    $this->writeMccc($fiche, $tabColonnes, $ligne);

                    $ligne++;
                }

                $this->excelWriter->colorCells('L' . $debutSae . ':W' . ($ligne - 1), 'FFCCCCCC');
                $this->excelWriter->colorCells('X23:AK' . $finRessource, 'FFCCCCCC');
                $this->excelWriter->colorCells('H23:H' . $finRessource, 'FFCCCCCC');
            }

            //suppression de la ligne modèle 18
            $this->excelWriter->removeRow(23);
        }

        // $this->genereReferentielCompetences($spreadsheet, $parcours, $formation);

        //supprimer la feuille de modèle
        $spreadsheet->removeSheetByIndex(0);
        $spreadsheet->setActiveSheetIndex(0);
        $spreadsheet->getActiveSheet()->setSelectedCells('A1');
        $this->excelWriter->setSpreadsheet($spreadsheet, true);

        $this->fileName = Tools::FileName('MCCC - ' . $anneeUniversitaire->getLibelle() . ' - ' . $formation->gettypeDiplome()?->getLibelleCourt() . ' ' . $parcours->getLibelle(), 40);
    }

    public function exportExcelbutMccc(
        AnneeUniversitaire $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface $dateEdition = null,
        bool               $versionFull = true
    ): StreamedResponse {
        $this->genereExcelbutMccc($anneeUniversitaire, $parcours, $dateEdition, $versionFull);
        return $this->excelWriter->genereFichier($this->fileName);
    }

    public function exportPdfbutMccc(
        AnneeUniversitaire $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface $dateEdition = null,
        bool               $versionFull = true
    ): StreamedResponse {
        $this->genereExcelbutMccc($anneeUniversitaire, $parcours, $dateEdition, $versionFull);
        return $this->excelWriter->genereFichierPdf($this->fileName);
    }

    public function exportAndSaveExcelbutMccc(
        AnneeUniversitaire $anneeUniversitaire,
        Parcours           $parcours,
        string             $dir,
        DateTimeInterface  $dateEdition,
        bool               $versionFull = true
    ): string {
        $this->genereExcelbutMccc($anneeUniversitaire, $parcours, $dateEdition, $versionFull);
        $this->excelWriter->saveFichier($this->fileName, $dir);
        return $this->fileName . '.xlsx';
    }

    private function genereReferentielCompetences(Spreadsheet $spreadsheet, Parcours $parcours, Formation $formation): void
    {
        $modele = $spreadsheet->getSheetByName(self::PAGE_REF_COMPETENCES);
        if ($modele === null) {
            throw new \Exception('Le modèle n\'existe pas');
        }

        //en-tête du fichier
        $modele->setCellValue(self::CEL_ANNEE_UNIVERSITAIRE, 'Année Universitaire ' . $formation->getAnneeUniversitaire()?->getLibelle());
        $modele->setCellValue(self::CEL_INTITULE_FORMATION, $formation->getDisplay());
        $modele->setCellValue(self::CEL_INTITULE_PARCOURS, $parcours->isParcoursDefaut() === false ? $parcours->getLibelle() : '');
        $modele->setCellValue(self::CEL_COMPOSANTE, $formation->getComposantePorteuse()?->getLibelle());
        $modele->setCellValue(self::CEL_SITE_FORMATION, $parcours->getLocalisation()?->getLibelle());

        $bccs = $parcours->getBlocCompetences();

        $ligne = 16;
        $this->excelWriter->setSheet($modele);
        foreach ($bccs as $bcc) {
            $this->excelWriter->writeCellXY(1, $ligne, $bcc->getCode());
            $this->excelWriter->writeCellXY(2, $ligne, $bcc->getLibelle(), ['wrap' => true]);
            $ligne++;
            foreach ($bcc->getCompetences() as $competence) {
                $this->excelWriter->writeCellXY(2, $ligne, $competence->getCode());
                $this->excelWriter->writeCellXY(3, $ligne, $competence->getLibelle(), ['wrap' => true]);

                $ligne++;
            }
        }

        $this->excelWriter->setPrintArea('A1:C' . $ligne);
    }


    private function writeMccc(mixed $fiche, array $tabColonnes, int $ligne): void
    {
        $mcccs = $fiche->getMcccs();
        foreach ($mcccs as $mccc) {
            if ($mccc->getLibelle() !== '' && array_key_exists($mccc->getLibelle(), $tabColonnes)) {
                $this->excelWriter->writeCellXY(
                //convertir chiffre en lettre excel
                    Coordinate::columnIndexFromString($tabColonnes[$mccc->getLibelle()]['pourcentage']),
                    $ligne,
                    $mccc->getPourcentage() === 0.0 ? '' : $mccc->getPourcentage() . '%',
                    ['style' => 'HORIZONTAL_CENTER']
                );
                $this->excelWriter->writeCellXY(
                    Coordinate::columnIndexFromString($tabColonnes[$mccc->getLibelle()]['nombre'] === 0 ? '' : $mccc->getNbEpreuves()),
                    $ligne,
                    $mccc->getNbEpreuves(),
                    ['style' => 'HORIZONTAL_CENTER']
                );
            }
        }
    }
}
