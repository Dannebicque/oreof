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
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Enums\RegimeInscriptionEnum;
use App\Repository\TypeEpreuveRepository;
use DateTimeInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LicenceMccc
{
    //todo: ajouter un watermark sur le doc ou une mention que la mention est définitive ou pas.
    //todo: gérer la date de vote

    // Pages
    public const PAGE_MODELE = 'modele';
    public const PAGE_REF_COMPETENCES = 'ref. compétences';
    // Cellules
    public const CEL_TYPE_FORMATION = 'J5';
    public const CEL_INTITULE_FORMATION = 'J6';
    public const CEL_INTITULE_PARCOURS = 'J7';
    public const CEL_ANNEE_ETUDE = 'J9';
    public  const CEL_COMPOSANTE = 'J11';
    public const CEL_SITE_FORMATION = 'J13';
    public const CEL_ANNEE_UNIVERSITAIRE = 'A3';
    public const CEL_RESPONSABLE_MENTION = 'E21';
    public const CEL_RESPONSABLE_PARCOURS = 'E22';
    public const CEL_REGIME_FI = 'D7';
    public const CEL_REGIME_FC = 'D9';
    public const CEL_REGIME_FI_APPRENTISSAGE = 'D11';
    public const CEL_REGIME_FC_CONTRAT_PRO = 'D13';

    //Colonnes sur Modèles

    public const COL_SEMESTRE = 1;
    public const COL_UE = 2;
    public const COL_INTITULE_UE = 3;
    public const COL_NUM_EC = 4;
    public const COL_INTITULE_EC = 5;
    public const COL_INTITULE_EC_EN = 6;
    public const COL_RESP_EC = 7;
    public const COL_LANGUE_EC = 8;
    public  const COL_SUPPORT_ANGLAIS = 9;
    public const COL_TYPE_EC = 10;
    // const COL_OBLIGATOIRE_OPTIONNEL = 11;
    public const COL_COURS_MUTUALISE = 11;
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
    public const COL_HEURES_AUTONOMIE = 22;
    public const COL_HEURES_TOTAL = 23;
    public const COL_MCCC_CC_POUCENTAGE = 24;
    public const COL_MCCC_CC_NB_EPREUVE = 25;
    public const COL_MCCC_ET_POUCENTAGE = 26;
    public const COL_MCCC_ET_TYPE_EPREUVE = 27;
    public const COL_MCCC_CCI_EPREUVES = 28;
    public const COL_MCCC_SECONDE_CHANCE = 29;
    const COL_DETAIL_TYPE_EPREUVES = "A24";


    protected array $typeEpreuves = [];
    private bool $versionFull = true;
    private string $fileName;

    public function __construct(
        protected ExcelWriter $excelWriter,
        TypeEpreuveRepository $typeEpreuveRepository
    ) {
        $epreuves = $typeEpreuveRepository->findAll();

        foreach ($epreuves as $epreuve) {
            $this->typeEpreuves[$epreuve->getId()] = $epreuve;
        }
    }


    public function genereExcelLicenceMccc(
        AnneeUniversitaire $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface  $dateEdition = null,
        bool               $versionFull = true
    ): void {
        //todo: gérer la date de publication et un "marquage" sur le document si pré-CFVU
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
        //ajoute les sigles
        $texte = '';
        foreach ($this->typeEpreuves as $typeEpreuve) {
            $texte .= $typeEpreuve->getSigle() . ' : ' . $typeEpreuve->getLibelle() . '; ';
        }
        $texte = substr($texte, 0, -2);
        $modele->setCellValue(self::COL_DETAIL_TYPE_EPREUVES, $texte);

        $index = 1;

        //recopie du modèle sur chaque année, puis remplissage
        foreach ($tabSemestresAnnee as $i => $semestres) {
            $clonedWorksheet = clone $modele;
            $clonedWorksheet->setTitle('Année ' . $i);
            $spreadsheet->addSheet($clonedWorksheet, $index);
            $index++;
            $anneeSheets[$i] = $clonedWorksheet;


            //remplissage de chaque année
            //ligne départ 18
            $ligne = 19;
            if (array_key_exists($i, $tabSemestresAnnee)) {
                $totalAnnee = new TotalVolumeHeure();
                $this->excelWriter->setSheet($clonedWorksheet);
                $this->excelWriter->writeCellName(self::CEL_ANNEE_ETUDE, $i . ' année');
                foreach ($tabSemestresAnnee[$i] as $semestre) {
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
            $this->excelWriter->mergeCellsCaR(1, $ligne+4, 20, $ligne +4);
            $this->excelWriter->setPrintArea('A1:AC' . $ligne+5);
        }
        $this->genereReferentielCompetences($spreadsheet, $parcours, $formation);

        //supprimer la feuille de modèle
        $spreadsheet->removeSheetByIndex(0);

        $this->excelWriter->setSpreadsheet($spreadsheet, true);
        //MCCC -2023-2024 -  M Psychologie sociale, du travail et des organisations
        $this->fileName = substr('MCCC - ' . $anneeUniversitaire->getLibelle() . ' - ' . $formation->gettypeDiplome()?->getLibelleCourt() . ' ' . $parcours->getLibelle(), 0, 30);
    }

    public function exportExcelLicenceMccc(
        AnneeUniversitaire $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface  $dateEdition = null,
        bool               $versionFull = true
    ): StreamedResponse {
        $this->genereExcelLicenceMccc($anneeUniversitaire, $parcours, $dateEdition, $versionFull);
        return $this->excelWriter->genereFichier($this->fileName);
    }

    public function exportPdfLicenceMccc(
        AnneeUniversitaire $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface  $dateEdition = null,
        bool               $versionFull = true
    ): StreamedResponse {
        $this->genereExcelLicenceMccc($anneeUniversitaire, $parcours, $dateEdition, $versionFull);
        return $this->excelWriter->genereFichierPdf($this->fileName);
    }

    public function exportAndSaveExcelLicenceMccc(
        AnneeUniversitaire $anneeUniversitaire,
        Parcours           $parcours,
        string             $dir,
        DateTimeInterface  $dateEdition,
        bool               $versionFull = true
    ): string {
        $this->genereExcelLicenceMccc($anneeUniversitaire, $parcours, $dateEdition, $versionFull);
        $this->excelWriter->saveFichier($this->fileName, $dir);
        return $this->fileName . '.xlsx';
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

    private function displayTypeEpreuve(array $typeE): string
    {
        $texte = '';
        foreach ($typeE as $type) {
            if ($type !== "" && $this->typeEpreuves[$type] !== null) {
                $texte .= $this->typeEpreuves[$type]->getSigle() . '; ';
            } else {
                $texte .= 'erreur épreuve; ';
            }
        }

        return substr($texte, 0, -2);
    }

    private function genereReferentielCompetences(Spreadsheet $spreadsheet, Parcours $parcours, Formation $formation): void
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
                $this->excelWriter->writeCellXY(self::COL_MCCC_CC_POUCENTAGE, $ligne, '100%');
                $this->excelWriter->writeCellXY(self::COL_MCCC_CC_NB_EPREUVE, $ligne, 2);
                if (array_key_exists(2, $mcccs) && array_key_exists('et', $mcccs[2]) && $mcccs[2]['et'] !== null) {
                    $this->excelWriter->writeCellXY(self::COL_MCCC_SECONDE_CHANCE, $ligne, $this->displayTypeEpreuve($mcccs[2]['et']->getTypeEpreuve()).' (' . $mcccs[2]['et']->getPourcentage() . '%)');
                } else {
                    $this->excelWriter->writeCellXY(self::COL_MCCC_SECONDE_CHANCE, $ligne, 'Erreur');
                    //todo: ajouter couleur dans ce cas ?
                }
                break;
            case 'cci':
                $texte = '';
                foreach ($mcccs as $mccc) {
                    $texte .= 'cc'.$mccc->getNumeroSession() . ' (' . $mccc->getPourcentage() . '%); ';
                }
                $texte = substr($texte, 0, -2);
                $this->excelWriter->writeCellXY(self::COL_MCCC_CCI_EPREUVES, $ligne, $texte);

                break;
            case 'cc_ct':
                if (array_key_exists(1, $mcccs) && array_key_exists('cc', $mcccs[1]) && $mcccs[1]['cc'] !== null) {
                    $this->excelWriter->writeCellXY(self::COL_MCCC_CC_POUCENTAGE, $ligne, $mcccs[1]['cc']->getPourcentage() . '%');
                    $this->excelWriter->writeCellXY(self::COL_MCCC_CC_NB_EPREUVE, $ligne, $mcccs[1]['cc']->getNbEpreuves());
                } else {
                    $this->excelWriter->writeCellXY(self::COL_MCCC_CC_POUCENTAGE, $ligne, 'Erreur');
                    $this->excelWriter->writeCellXY(self::COL_MCCC_CC_NB_EPREUVE, $ligne, 'Erreur');
                }

                if (array_key_exists(1, $mcccs) && array_key_exists('et', $mcccs[1]) && $mcccs[1]['et'] !== null) {
                    $this->excelWriter->writeCellXY(self::COL_MCCC_ET_POUCENTAGE, $ligne, $mcccs[1]['et']->getPourcentage() . '%');
                    $this->excelWriter->writeCellXY(self::COL_MCCC_ET_TYPE_EPREUVE, $ligne, $this->displayTypeEpreuve($mcccs[1]['et']->getTypeEpreuve()));
                } else {
                    $this->excelWriter->writeCellXY(self::COL_MCCC_ET_POUCENTAGE, $ligne, 'Erreur');
                    $this->excelWriter->writeCellXY(self::COL_MCCC_ET_TYPE_EPREUVE, $ligne, 'Erreur');
                }

                if (array_key_exists(2, $mcccs) && array_key_exists('et', $mcccs[2]) && $mcccs[2]['et'] !== null) {
                    $this->excelWriter->writeCellXY(self::COL_MCCC_SECONDE_CHANCE, $ligne, $this->displayTypeEpreuve($mcccs[2]['et']->getTypeEpreuve()).' (' . $mcccs[2]['et']->getPourcentage() . '%)');
                } else {
                    $this->excelWriter->writeCellXY(self::COL_MCCC_SECONDE_CHANCE, $ligne, 'Erreur');
                }
                break;
            case 'ct':
                $this->excelWriter->writeCellXY(self::COL_MCCC_ET_POUCENTAGE, $ligne, '100%');
                if (array_key_exists(1, $mcccs) && array_key_exists('et', $mcccs[1]) && $mcccs[1]['et'] !== null) {
                    $this->excelWriter->writeCellXY(self::COL_MCCC_ET_TYPE_EPREUVE, $ligne, $this->displayTypeEpreuve($mcccs[1]['et']->getTypeEpreuve()));
                } else {
                    $this->excelWriter->writeCellXY(self::COL_MCCC_ET_TYPE_EPREUVE, $ligne, 'Erreur');
                }

                if (array_key_exists(2, $mcccs) && array_key_exists('et', $mcccs[2]) && $mcccs[2]['et'] !== null) {
                    $this->excelWriter->writeCellXY(self::COL_MCCC_SECONDE_CHANCE, $ligne, $this->displayTypeEpreuve($mcccs[2]['et']->getTypeEpreuve()).' (' . $mcccs[2]['et']->getPourcentage() . '%)');
                } else {
                    $this->excelWriter->writeCellXY(self::COL_MCCC_SECONDE_CHANCE, $ligne, 'Erreur');
                }


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
