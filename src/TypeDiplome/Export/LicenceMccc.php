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
use App\Entity\AnneeUniversitaire;
use App\Entity\ElementConstitutif;
use App\Entity\Mccc;
use App\Entity\Parcours;
use App\Enums\RegimeInscriptionEnum;
use App\Repository\TypeEpreuveRepository;
use App\TypeDiplome\Source\LicenceTypeDiplome;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;

class LicenceMccc
{
    // Pages
    const PAGE_MODELE = 'modele';
    const PAGE_REF_COMPETENCES = 'ref. compétences';
    // Cellules
    const CEL_TYPE_FORMATION = 'J5';
    const CEL_INTITULE_FORMATION = 'J6';
    const CEL_INTITULE_PARCOURS = 'J7';
    const CEL_ANNEE_ETUDE = 'J9';
    const CEL_COMPOSANTE = 'J11';
    const CEL_SITE_FORMATION = 'J13';
    const CEL_ANNEE_UNIVERSITAIRE = 'A3';
    const CEL_RESPONSABLE_MENTION = 'E21';
    const CEL_RESPONSABLE_PARCOURS = 'E22';
    const CEL_REGIME_FI = 'C7';
    const CEL_REGIME_FC = 'C9';
    const CEL_REGIME_FI_APPRENTISSAGE = 'C11';
    const CEL_REGIME_FC_CONTRAT_PRO = 'C13';

    //Colonnes sur Modèles

    const COL_SEMESTRE = 1;
    const COL_UE = 2;
    const COL_INTITULE_UE = 3;
    const COL_NUM_EC = 4;
    const COL_INTITULE_EC = 5;
    const COL_INTITULE_EC_EN = 6;
    const COL_RESP_EC = 7;
    const COL_LANGUE_EC = 8;
    const COL_SUPPORT_ANGLAIS = 9;
    const COL_TYPE_EC = 10;
   // const COL_OBLIGATOIRE_OPTIONNEL = 11;
    const COL_COURS_MUTUALISE = 11;
    const COL_COMPETENCES = 12;
    const COL_ECTS = 13;
    const COL_HEURES_PRES_CM = 14;
    const COL_HEURES_PRES_TD = 15;
    const COL_HEURES_PRES_TP = 16;
    const COL_HEURES_PRES_TOTAL = 17;

    const COL_HEURES_DIST_CM = 18;
    const COL_HEURES_DIST_TD = 19;
    const COL_HEURES_DIST_TP = 20;
    const COL_HEURES_DIST_TOTAL = 21;
    const COL_HEURES_AUTONOMIE = 22;
    const COL_HEURES_TOTAL = 23;
    const COL_MCCC_CC_POUCENTAGE = 24;
    const COL_MCCC_CC_NB_EPREUVE = 25;
    const COL_MCCC_ET_POUCENTAGE = 26;
    const COL_MCCC_ET_TYPE_EPREUVE = 27;
    const COL_MCCC_CCI_EPREUVES = 28;
    const COL_MCCC_SECONDE_CHANCE = 29;


    protected array $typeEpreuves = [];
    private bool $versionFull = true;

    public function __construct(
        protected ExcelWriter $excelWriter,
        TypeEpreuveRepository $typeEpreuveRepository
    ) {
        $epreuves = $typeEpreuveRepository->findAll();

        foreach ($epreuves as $epreuve) {
            $this->typeEpreuves[$epreuve->getId()] = $epreuve;
        }
    }


    public function exportExcelLicenceMccc(
        AnneeUniversitaire $anneeUniversitaire,
        Parcours           $parcours,
        bool               $versionFull = true
    ) {
        $this->versionFull = $versionFull;
        $formation = $parcours->getFormation();

        $redStyle = new Style(false, true);
        $redStyle->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getEndColor()->setARGB(Color::COLOR_RED);
        $redStyle->getFont()->setColor(new Color(Color::COLOR_WHITE));

        if (null === $formation) {
            throw new \Exception('La formation n\'existe pas');
        }
        $spreadsheet = $this->excelWriter->createFromTemplate('Annexe_MCCC.xlsx');

        $this->genereReferentielCompetences($spreadsheet, $parcours, $formation);

        // Prépare le modèle avant de dupliquer
        $modele = $spreadsheet->getSheetByName(self::PAGE_MODELE);
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
        $modele->setCellValue(self::CEL_ANNEE_UNIVERSITAIRE, 'Année Universitaire ' . $formation->getAnneeUniversitaire()?->getLibelle());
        $modele->setCellValue(self::CEL_TYPE_FORMATION, $formation->getTypeDiplome()?->getLibelle());
        $modele->setCellValue(self::CEL_INTITULE_FORMATION, $formation->getDisplay());
        $modele->setCellValue(self::CEL_INTITULE_PARCOURS, $parcours->isParcoursDefaut() === false ? $parcours->getLibelle() : '');
        $modele->setCellValue(self::CEL_COMPOSANTE, $formation->getComposantePorteuse()?->getLibelle());
        if ($formation->isHasParcours() === false) {
            $modele->setCellValue(self::CEL_SITE_FORMATION, $formation->getLocalisationMention()[0]?->getLibelle());
        } else {
            $modele->setCellValue(self::CEL_SITE_FORMATION, $parcours->getLocalisation()?->getLibelle());
        }
        $modele->setCellValue(self::CEL_RESPONSABLE_MENTION, $formation->getResponsableMention()?->getDisplay());
        $modele->setCellValue(self::CEL_RESPONSABLE_PARCOURS, $parcours->getRespParcours()?->getDisplay());

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
                $this->excelWriter->setSheet($clonedWorksheet);
                $this->excelWriter->writeCellName(self::CEL_ANNEE_ETUDE, $i . ' année');
                foreach ($tabSemestresAnnee[$i] as $key => $semestre) {
                    $debutSemestre = $ligne;
                    foreach ($semestre->getSemestre()->getUes() as $ue) {
                        //UE
                        if ($ue->getUeParent() === null) {
                            $debut = $ligne;
                            foreach ($ue->getElementConstitutifs() as $ec) {
                                if ($ec->getEcParent() === null) {
                                    $ligne = $this->afficheEc($ligne, $ec, $totalAnnee);
                                    foreach ($ec->getEcEnfants() as $ece) {
                                        $ligne = $this->afficheEc($ligne, $ece, $totalAnnee);
                                    }
                                }
                            }

                            if ($debut < $ligne - 1) {
                                //    dump(self::COL_UE.'-'. $debut.'-'. self::COL_UE.'-'. $ligne - 1);
                                $this->excelWriter->mergeCellsCaR(self::COL_UE, $debut, self::COL_UE, $ligne - 1);
//
                                $this->excelWriter->mergeCellsCaR(self::COL_INTITULE_UE, $debut, self::COL_INTITULE_UE, $ligne - 1);
                            }
                            $this->excelWriter->writeCellXY(self::COL_UE, $debut, $ue->display($parcours), ['wrap' => true]);
                            $this->excelWriter->writeCellXY(self::COL_INTITULE_UE, $debut, $ue->getLibelle(), ['wrap' => true]);
                            foreach ($ue->getUeEnfants() as $uee) {
                                $debut = $ligne;
                                foreach ($uee->getElementConstitutifs() as $ec) {
                                    if ($ec->getEcParent() === null) {
                                        $ligne = $this->afficheEc($ligne, $ec, $totalAnnee);
                                        foreach ($ec->getEcEnfants() as $ece) {
                                            $ligne = $this->afficheEc($ligne, $ece, $totalAnnee);
                                        }
                                    }
                                }

                                if ($debut < $ligne - 1) {
                                    //    dump(self::COL_UE.'-'. $debut.'-'. self::COL_UE.'-'. $ligne - 1);
                                    $this->excelWriter->mergeCellsCaR(self::COL_UE, $debut, self::COL_UE, $ligne - 1);
//
                                    $this->excelWriter->mergeCellsCaR(self::COL_INTITULE_UE, $debut, self::COL_INTITULE_UE, $ligne - 1);
                                }
                                $this->excelWriter->writeCellXY(self::COL_UE, $debut, $uee->display($parcours), ['wrap' => true]);
                                $this->excelWriter->writeCellXY(self::COL_INTITULE_UE, $debut, $uee->getLibelle(), ['wrap' => true]);
                            }
                        }
                    }

                    $this->excelWriter->mergeCellsCaR(self::COL_SEMESTRE, $debutSemestre, self::COL_SEMESTRE, $ligne - 1);
                    $this->excelWriter->writeCellXY(self::COL_SEMESTRE, $debutSemestre, 'S' . $semestre->getOrdre());

                    //dump(self::COL_SEMESTRE.'*-*'. $debutSemestre.'*-*'. self::COL_SEMESTRE.'*-*'. $ligne - 1);
                    $this->excelWriter->writeCellXY(self::COL_HEURES_PRES_CM, $ligne, $totalAnnee->totalCmPresentiel, ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_HEURES_PRES_TD, $ligne, $totalAnnee->totalTdPresentiel, ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_HEURES_PRES_TP, $ligne, $totalAnnee->totalTpPresentiel, ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_HEURES_PRES_TOTAL, $ligne, $totalAnnee->getTotalPresentiel(), ['style' => 'HORIZONTAL_CENTER']);

                    //si pas distanciel, griser...

                    $this->excelWriter->writeCellXY(self::COL_HEURES_DIST_CM, $ligne, $totalAnnee->totalCmDistanciel, ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_HEURES_DIST_TD, $ligne, $totalAnnee->totalTdDistanciel, ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_HEURES_DIST_TP, $ligne, $totalAnnee->totalTpDistanciel, ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_HEURES_DIST_TOTAL, $ligne, $totalAnnee->getTotalDistanciel(), ['style' => 'HORIZONTAL_CENTER']);

                    $this->excelWriter->writeCellXY(self::COL_HEURES_TOTAL, $ligne, $totalAnnee->getVolumeTotal(), ['style' => 'HORIZONTAL_CENTER']);

                    $this->excelWriter->writeCellXY(self::COL_HEURES_AUTONOMIE, $ligne + 1, $totalAnnee->getTotalVolumeTe(), ['style' => 'HORIZONTAL_CENTER']);

                    $this->excelWriter->writeCellXY(self::COL_HEURES_PRES_CM, $ligne + 2, $totalAnnee->getTotalEtudiant(), ['style' => 'HORIZONTAL_CENTER']);
                }
            }
            //suppression de la ligne modèle 18
            $this->excelWriter->removeRow(18);
            $this->updateIfNotFull();
        }


        //supprimer la feuille de modèle
        $spreadsheet->removeSheetByIndex(0);

        $this->excelWriter->setSpreadsheet($spreadsheet, true);
        //MCCC -2023-2024 -  M Psychologie sociale, du travail et des organisations
        return $this->excelWriter->genereFichier(substr('MCCC - ' . $anneeUniversitaire->getLibelle() . ' - ' . $formation->gettypeDiplome()?->getLibelleCourt() . ' ' . $parcours->getLibelle(), 0, 30));
    }

    public function getMcccs(ElementConstitutif $elementConstitutif): array
    {
        $mcccs = $elementConstitutif->getMcccs();
        $tabMcccs = [];

        if ($elementConstitutif->getTypeMccc() === 'cci') {
            foreach ($mcccs as $mccc) {
                $tabMcccs[$mccc->getNumeroSession()] = $mccc;
            }
        } else {
            foreach ($mcccs as $mccc) {
                if ($mccc->isSecondeChance()) {
                    $tabMcccs[3]['chance'] = $mccc;
                } elseif ($mccc->isControleContinu() === true && $mccc->isExamenTerminal() === false) {
                    $tabMcccs[$mccc->getNumeroSession()]['cc'] = $mccc;
                } elseif ($mccc->isControleContinu() === false && $mccc->isExamenTerminal() === true) {
                    $tabMcccs[$mccc->getNumeroSession()]['et'] = $mccc;
                }
            }
        }

        return $tabMcccs;
    }

    private function displayTypeEpreuve($typeE): string
    {
        $texte = '';
        foreach ($typeE as $type) {
            $texte .= $this->typeEpreuves[$type]->getSigle() . '; ';
        }

        return substr($texte, 0, -2);
    }

    private function genereReferentielCompetences($spreadsheet, $parcours, $formation): void
    {
        $modele = $spreadsheet->getSheetByName(self::PAGE_REF_COMPETENCES);
        if ($modele === null) {
            throw new \Exception('Le modèle n\'existe pas');
        }

        //en-tête du fichier
        $modele->setCellValue(self::CEL_ANNEE_UNIVERSITAIRE, 'Année Universitaire ' . $formation->getAnneeUniversitaire()?->getLibelle());
        $modele->setCellValue(self::CEL_TYPE_FORMATION, $formation->getTypeDiplome()?->getLibelle());
        $modele->setCellValue(self::CEL_INTITULE_FORMATION, $formation->getDisplay());
        $modele->setCellValue(self::CEL_INTITULE_PARCOURS, $parcours->isParcoursDefaut() === false ? $parcours->getLibelle() : '');
        $modele->setCellValue(self::CEL_COMPOSANTE, $formation->getComposantePorteuse()?->getLibelle());
        $modele->setCellValue(self::CEL_SITE_FORMATION, $parcours->getLocalisation()?->getLibelle());

        $bccs = $parcours->getBlocCompetences();

        $ligne = 16;
        $this->excelWriter->setSheet($modele);
        foreach ($bccs as $bcc) {
            $this->excelWriter->writeCellXY(1, $ligne, $bcc->getCode());
            $this->excelWriter->writeCellXY(2, $ligne, $bcc->getLibelle());
            $ligne++;
            foreach ($bcc->getCompetences() as $competence) {
                $this->excelWriter->writeCellXY(2, $ligne, $competence->getCode());
                $this->excelWriter->writeCellXY(3, $ligne, $competence->getLibelle());
                $ligne++;
            }
        }
    }

    /**
     * @param int $ligne
     * @param int $num
     * @param mixed $ec
     * @param TotalVolumeHeure $totalAnnee
     * @return int
     */
    private function afficheEc(int $ligne, ElementConstitutif $ec, TotalVolumeHeure $totalAnnee): int
    {
        $this->excelWriter->insertNewRowBefore($ligne);
        $this->excelWriter->writeCellXY(self::COL_NUM_EC, $ligne, $ec->getCode());//todo: gérer les cas

        if ($ec->getFicheMatiere() !== null) {
            //todo: plutôt un test sur le type EC
            $this->excelWriter->writeCellXY(self::COL_INTITULE_EC, $ligne, $ec->getFicheMatiere()->getLibelle(), ['wrap' => true]);//todo: gérer les cas
            $this->excelWriter->writeCellXY(self::COL_INTITULE_EC_EN, $ligne, $ec->getFicheMatiere()->getLibelleAnglais(), ['wrap' => true]);
            $this->excelWriter->writeCellXY(self::COL_RESP_EC, $ligne, $ec->getFicheMatiere()->getResponsableFicheMatiere()?->getDisplay(), ['wrap' => true]);

            // langue
            $texte = '';
            foreach ($ec->getFicheMatiere()->getLangueDispense() as $langue) {
                $texte .= $langue->getLibelle() . "; ";
            }
            $texte = substr($texte, 0, -2);
            $this->excelWriter->writeCellXY(self::COL_LANGUE_EC, $ligne, $texte);

            $anglais = false;
            foreach ($ec->getFicheMatiere()->getLangueSupport() as $langue) {
                if (strtolower($langue->getCodeIso()) === 'en') {
                    $anglais = true;
                }
            }
            $this->excelWriter->writeCellXY(self::COL_SUPPORT_ANGLAIS, $ligne, $anglais === true ? 'O' : 'N');

            $this->excelWriter->writeCellXY(self::COL_COURS_MUTUALISE, $ligne, $ec->getFicheMatiere()->isEnseignementMutualise() === true ? 'O' : 'N');


            // BCC
            $texte = '';
            foreach ($ec->getFicheMatiere()->getCompetences() as $comp) {
                $texte .= $comp->getCode() . "; ";
            }

            $texte = substr($texte, 0, -2);

            $this->excelWriter->writeCellXY(self::COL_COMPETENCES, $ligne, $texte);
        } else {
            $this->excelWriter->writeCellXY(self::COL_INTITULE_EC, $ligne, $ec->getTexteEcLibre(), ['wrap' => true]);
        }
        // MCCC

        $mcccs = $this->getMcccs($ec);

        switch ($ec->getTypeMccc()) {
            case 'cc':
                $this->excelWriter->writeCellXY(self::COL_MCCC_CC_POUCENTAGE, $ligne, '50%');
                $this->excelWriter->writeCellXY(self::COL_MCCC_CC_NB_EPREUVE, $ligne, 2);
                $this->excelWriter->writeCellXY(self::COL_MCCC_SECONDE_CHANCE, $ligne, $this->displayTypeEpreuve($mcccs[2]['et']->getTypeEpreuve()));
                break;
            case 'cci':
                $texte = '';
                foreach ($mcccs as $mccc) {
                    $texte .= $this->displayTypeEpreuve($mccc->getTypeEpreuve()) . '(' . $mccc->getPourcentage() . '%; ';
                }
                $texte = substr($texte, 0, -2);
                $this->excelWriter->writeCellXY(self::COL_MCCC_CCI_EPREUVES, $ligne, $texte);

                break;
            case 'cc_ct':
                $this->excelWriter->writeCellXY(self::COL_MCCC_CC_POUCENTAGE, $ligne, $mcccs[1]['cc']->getPourcentage());
                $this->excelWriter->writeCellXY(self::COL_MCCC_CC_NB_EPREUVE, $ligne, $mcccs[1]['cc']->getNbEpreuves());
                $this->excelWriter->writeCellXY(self::COL_MCCC_ET_POUCENTAGE, $ligne, $this->displayTypeEpreuve($mcccs[1]['et']->getTypeEpreuve()));
                $this->excelWriter->writeCellXY(self::COL_MCCC_ET_TYPE_EPREUVE, $ligne, $this->displayTypeEpreuve($mcccs[1]['et']->getTypeEpreuve()));

                break;
            case 'ct':
                $this->excelWriter->writeCellXY(self::COL_MCCC_ET_POUCENTAGE, $ligne, '100%');
                $this->excelWriter->writeCellXY(self::COL_MCCC_ET_TYPE_EPREUVE, $ligne, $this->displayTypeEpreuve($mcccs[1]['et']->getTypeEpreuve()));
                $this->excelWriter->writeCellXY(self::COL_MCCC_SECONDE_CHANCE, $ligne, $this->displayTypeEpreuve($mcccs[2]['et']->getTypeEpreuve()));
                break;
        }


        $this->excelWriter->writeCellXY(self::COL_TYPE_EC, $ligne, $ec->getTypeEc() ? $ec->getTypeEc()->getLibelle() : '');

        // Heures
        $this->excelWriter->writeCellXY(self::COL_ECTS, $ligne, $ec->getEcts());
        $this->excelWriter->writeCellXY(self::COL_HEURES_PRES_CM, $ligne, $ec->getVolumeCmPresentiel());
        $this->excelWriter->writeCellXY(self::COL_HEURES_PRES_TD, $ligne, $ec->getVolumeTdPresentiel());
        $this->excelWriter->writeCellXY(self::COL_HEURES_PRES_TP, $ligne, $ec->getVolumeTpPresentiel());
        $this->excelWriter->writeCellXY(self::COL_HEURES_PRES_TOTAL, $ligne, $ec->volumeTotalPresentiel());

        //si pas distanciel, griser...
        $this->excelWriter->writeCellXY(self::COL_HEURES_DIST_CM, $ligne, $ec->getVolumeCmDistanciel(), ['bgcolor' => 'cccccc']);
        $this->excelWriter->writeCellXY(self::COL_HEURES_DIST_TD, $ligne, $ec->getVolumeTdDistanciel());
        $this->excelWriter->writeCellXY(self::COL_HEURES_DIST_TP, $ligne, $ec->getVolumeTpDistanciel());
        $this->excelWriter->writeCellXY(self::COL_HEURES_DIST_TOTAL, $ligne, $ec->volumeTotalDistanciel());
        $this->excelWriter->writeCellXY(self::COL_HEURES_AUTONOMIE, $ligne, $ec->getVolumeTe());

        $this->excelWriter->writeCellXY(self::COL_HEURES_TOTAL, $ligne, $ec->volumeTotal());
        $totalAnnee->addEc($ec);

        $this->excelWriter->getRowAutosize($ligne);

        $ligne++;
        //dump($ligne);


        return $ligne;
    }

    private function updateIfNotFull(): void
    {
        if ($this->versionFull === false) {
            //décalage des données de formation
            $this->excelWriter->copyFromCellToCell('I5', 'R5');
            $this->excelWriter->copyFromCellToCell('J5', 'S5');

            $this->excelWriter->copyFromCellToCell('I6', 'R6');
            $this->excelWriter->copyFromCellToCell('J6', 'S6');

            $this->excelWriter->copyFromCellToCell('I7', 'R7');
            $this->excelWriter->copyFromCellToCell('J7', 'S7');

            $this->excelWriter->copyFromCellToCell('I9', 'R9');
            $this->excelWriter->copyFromCellToCell('J9', 'S9');

            $this->excelWriter->copyFromCellToCell('I11', 'R11');
            $this->excelWriter->copyFromCellToCell('J11', 'S11');

            $this->excelWriter->copyFromCellToCell('I13', 'R13');
            $this->excelWriter->copyFromCellToCell('J13', 'S13');

            //suppression des colonnes
            $this->excelWriter->removeColumn('F', 8);
        }
    }
}
