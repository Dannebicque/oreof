<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/Export/AbstractLicenceMcc.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 19/05/2024 20:35
 */

namespace App\TypeDiplome\Export;

use App\Classes\Excel\ExcelWriter;
use DateTimeInterface;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbstractLicenceMccc
{
    // Pages
    public const PAGE_MODELE = 'modele';
    public const PAGE_REF_COMPETENCES = 'ref. compétences';
    // Cellules
    public const CEL_TYPE_FORMATION = 'J5';
    public const CEL_INTITULE_FORMATION = 'J6';
    public const CEL_INTITULE_PARCOURS = 'J7';
    public const CEL_ANNEE_ETUDE = 'J9';
    public const CEL_COMPOSANTE = 'J11';
    public const CEL_SITE_FORMATION = 'J13';
    public const CEL_ANNEE_UNIVERSITAIRE = 'A3';
    public const CEL_RESPONSABLE_MENTION = 'E24';
    public const CEL_RESPONSABLE_PARCOURS = 'E25';
    public const CEL_REGIME_FI = 'D7';
    public const CEL_REGIME_FC = 'D9';
    public const CEL_REGIME_FI_APPRENTISSAGE = 'D11';
    public const CEL_REGIME_FC_CONTRAT_PRO = 'D13';

    public const CEL_DATE_CFVU = 'AB25';
    public const CEL_DATE_CONSEIL = 'AB24';

    //Colonnes sur Modèles

    public const COL_SEMESTRE = 1;
    public const COL_UE = 2;
    public const COL_INTITULE_UE = 3;
    public const COL_NUM_EC = 4;
    public const COL_INTITULE_EC = 5;
    public const COL_INTITULE_EC_EN = 6;
    public const COL_RESP_EC = 7;
    public const COL_LANGUE_EC = 8;
    public const COL_SUPPORT_ANGLAIS = 9;
    public const COL_TYPE_EC = 11;
    public const COL_COURS_MUTUALISE = 10;
    public const COL_COMPETENCES = 12;
    public const COL_ECTS = 13;
    public const COL_HEURES_PRES_CM = 14;
    public const COL_HEURES_PRES_TD = 15;
    public const COL_HEURES_PRES_TP = 16;
    public const COL_HEURES_PRES_TOTAL = 17;

    public const COL_HEURES_DIST_CM = 18;
    public const COL_HEURES_DIST_TD = 19;
    public const COL_HEURES_DIST_TP = 20;
    public const COL_HEURES_DIST_TOTAL = 21;
    public const COL_HEURES_AUTONOMIE = 23;
    public const COL_HEURES_TOTAL = 22;
    public const COL_MCCC_CCI = 24;
    public const COL_MCCC_CC = 25;
    public const COL_MCCC_CT = 26;
    public const COL_MCCC_SECONDE_CHANCE_CC_SANS_TP = 27;
    public const COL_MCCC_SECONDE_CHANCE_CC_AVEC_TP = 28;
    public const COL_MCCC_SECONDE_CHANCE_CC_SUP_10 = 29;
    public const COL_MCCC_SECONDE_CHANCE_CT = 30;

    public const COL_DETAIL_TYPE_EPREUVES = "A27";

    protected array $typeEpreuves = [];
    protected bool $versionFull = true;
    protected string $fileName;
    protected array $lignesSemestre = [];
    protected array $lignesEcColorees = [];

    private string $dir;

    public function __construct(
        protected ExcelWriter             $excelWriter,
    ) {
    }

    protected function getTypeEpreuves(): void
    {
        $epreuves = $this->typeEpreuveRepository->findAll();
        foreach ($epreuves as $epreuve) {
            $this->typeEpreuves[$epreuve->getId()] = $epreuve;
        }
    }

    protected function updateIfNotFull(): void
    {
        if ($this->versionFull === false) {
            //décalage des données de formation
            $this->excelWriter->unMergeCells('G5:I5');
            $this->excelWriter->unMergeCells('G6:I6');
            $this->excelWriter->unMergeCells('G7:I7');
            $this->excelWriter->unMergeCells('G9:I9');
            $this->excelWriter->unMergeCells('G11:I11');
            $this->excelWriter->unMergeCells('G13:I13');

            $this->excelWriter->unMergeCells('J5:L5');
            $this->excelWriter->unMergeCells('J6:L6');
            $this->excelWriter->unMergeCells('J7:L7');
            $this->excelWriter->unMergeCells('J9:L9');
            $this->excelWriter->unMergeCells('J11:L11');
            $this->excelWriter->unMergeCells('J13:L13');

          //  $this->excelWriter->copyFromCellToCell('G5', 'M5');
            $this->excelWriter->copyFromCellToCell('J5', 'S5');
          //  $this->excelWriter->copyFromCellToCell('G6', 'M6');
            $this->excelWriter->copyFromCellToCell('J6', 'S6');
          //  $this->excelWriter->copyFromCellToCell('G7', 'M7');
            $this->excelWriter->copyFromCellToCell('J7', 'S7');
          //  $this->excelWriter->copyFromCellToCell('G9', 'M9');
            $this->excelWriter->copyFromCellToCell('J9', 'S9');
           // $this->excelWriter->copyFromCellToCell('G11', 'M11');
            $this->excelWriter->copyFromCellToCell('J11', 'S11');
          //  $this->excelWriter->copyFromCellToCell('G13', 'M13');
            $this->excelWriter->copyFromCellToCell('J13', 'S13');

            $this->excelWriter->unMergeCells('D15:M16');
            foreach ($this->lignesSemestre as $ligneSemestre) {
                $ligneSemestre--;
                $this->excelWriter->unMergeCells('B' . $ligneSemestre . ':L' . $ligneSemestre);
            }
            //suppression des colonnes
            $this->excelWriter->removeColumn('F', 5);
            $this->excelWriter->mergeCells('D15:H16');
            $this->excelWriter->mergeCells('G5:L5');
            $this->excelWriter->mergeCells('G6:L6');
            $this->excelWriter->mergeCells('G7:L7');
            $this->excelWriter->mergeCells('G9:L9');
            $this->excelWriter->mergeCells('G11:L11');
            $this->excelWriter->mergeCells('G13:L13');

            $this->excelWriter->writeCellName('G5', 'Type de formation :');
            $this->excelWriter->writeCellName('G6', 'Intitulé de la mention (Spécialité pour les BUT) :');
            $this->excelWriter->writeCellName('G7', 'Intitulé du parcours (si existant) :');
            $this->excelWriter->writeCellName('G9', 'Année d\'études :');
            $this->excelWriter->writeCellName('G11', 'Composante :');
            $this->excelWriter->writeCellName('G13', 'Site de formation :');

            $style = ['alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ]];

            $this->excelWriter->cellStyle('G5', $style);
            $this->excelWriter->cellStyle('G6', $style);
            $this->excelWriter->cellStyle('G7', $style);
            $this->excelWriter->cellStyle('G9', $style);
            $this->excelWriter->cellStyle('G11', $style);
            $this->excelWriter->cellStyle('G13', $style);

            $this->excelWriter->mergeCells('N5:S5');
            $this->excelWriter->mergeCells('N6:S6');
            $this->excelWriter->mergeCells('N7:S7');
            $this->excelWriter->mergeCells('N9:S9');
            $this->excelWriter->mergeCells('N11:S11');
            $this->excelWriter->mergeCells('N13:S13');

            foreach ($this->lignesSemestre as $ligneSemestre) {
                $ligneSemestre--;
                $this->excelWriter->mergeCells('B' . $ligneSemestre . ':G' . $ligneSemestre);
            }
        }
    }

    protected function displayDuree(?DateTimeInterface $duree): string
    {
        if ($duree === null) {
            return '';
        }

        return $duree->format('H\hi');
    }

}
