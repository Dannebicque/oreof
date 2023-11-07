<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/Export/LicenceMccc.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 24/05/2023 16:14
 */

namespace App\TypeDiplome\Export;

use App\Classes\CalculStructureParcours;
use App\Classes\Excel\ExcelWriter;
use App\DTO\StructureEc;
use App\DTO\StructureSemestre;
use App\DTO\StructureUe;
use App\DTO\TotalVolumeHeure;
use App\Entity\AnneeUniversitaire;
use App\Entity\Formation;
use App\Entity\Mccc;
use App\Entity\Parcours;
use App\Enums\RegimeInscriptionEnum;
use App\Repository\TypeEpreuveRepository;
use App\Utils\Tools;
use DateTimeInterface;
use Gotenberg\Gotenberg;
use Gotenberg\Stream;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;

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
    public const CEL_COMPOSANTE = 'J11';
    public const CEL_SITE_FORMATION = 'J13';
    public const CEL_ANNEE_UNIVERSITAIRE = 'A3';
    public const CEL_RESPONSABLE_MENTION = 'E24';
    public const CEL_RESPONSABLE_PARCOURS = 'E25';
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
    private bool $versionFull = true;
    private string $fileName;
    private array $lignesSemestre = [];
    private array $lignesEcColorees = [];

    private string $dir;

    public function __construct(
        KernelInterface                   $kernel,
        protected ClientInterface         $client,
        protected CalculStructureParcours $calculStructureParcours,
        protected ExcelWriter             $excelWriter,
        TypeEpreuveRepository             $typeEpreuveRepository
    ) {
        $epreuves = $typeEpreuveRepository->findAll();
        $this->dir = $kernel->getProjectDir() . '/public';
        foreach ($epreuves as $epreuve) {
            $this->typeEpreuves[$epreuve->getId()] = $epreuve;
        }
    }


    /**
     * @throws Exception
     * @throws \Exception
     */
    public function genereExcelLicenceMccc(
        AnneeUniversitaire $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface $dateEdition = null,
        bool               $versionFull = true
    ): void {
        //todo: gérer la date de publication et un "marquage" sur le document si pré-CFVU
        $this->versionFull = $versionFull;
        $formation = $parcours->getFormation();
        $parcours1 = $parcours;
        $dto = $this->calculStructureParcours->calcul($parcours1);
        $totalFormation = $dto->heuresEctsFormation;

        if (null === $formation) {
            throw new \Exception('La formation n\'existe pas');
        }
        $this->excelWriter->createFromTemplate('Annexe_MCCC.xlsx');

        // Prépare le modèle avant de dupliquer
        $modele = $this->excelWriter->getSheetByName(self::PAGE_MODELE);
        if ($modele === null) {
            throw new \Exception('Le modèle n\'existe pas');
        }

        //récupération des données
        // récupération des semestres du parcours puis classement par année et par ordre
        $tabSemestresAnnee = $dto->getTabAnnee();

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
        $texte .= '. CCr : Note globale de CC de première session conservée. TPr : Note globale de TP de première session conservée.';
        $modele->setCellValue(self::COL_DETAIL_TYPE_EPREUVES, $texte);

        $index = 1;

        foreach ($tabSemestresAnnee as $i => $semestres) {
            $clonedWorksheet = clone $modele;
            $clonedWorksheet->setTitle('Année ' . $i);
            $this->excelWriter->addSheet($clonedWorksheet, $index);
            $index++;
            $anneeSheets[$i] = $clonedWorksheet;


            //remplissage de chaque année
            //ligne départ 18
            $ligne = 19;
            if (array_key_exists($i, $tabSemestresAnnee)) {
                $totalAnnee = new TotalVolumeHeure();
                $this->excelWriter->setSheet($clonedWorksheet);
                $this->excelWriter->writeCellName(self::CEL_ANNEE_ETUDE, $i . ' année');
                $this->lignesSemestre = [];
                $this->lignesEcColorees = [];
                /** @var StructureSemestre $semestre */
                foreach ($semestres as $semestre) {
                    $totalAnnee->addSemestre($semestre->heuresEctsSemestre);
                    $debutSemestre = $ligne;
                    foreach ($semestre->ues as $ue) {
                        //UE
                        $debut = $ligne;
                        if (count($ue->uesEnfants) === 0) {
                            if ($ue->ue->getNatureUeEc() !== null && $ue->ue->getNatureUeEc()->isLibre()) {
                                $ligne = $this->afficheUeLibre($ligne, $ue);
                            } else {
                                //Si des UE enfants, on affiche pas les éventuels EC résiduels,
                                foreach ($ue->elementConstitutifs as $ec) {
                                    $ligne = $this->afficheEc($ligne, $ec);
                                    foreach ($ec->elementsConstitutifsEnfants as $ece) {
                                        $ligne = $this->afficheEc($ligne, $ece);
                                    }
                                }


                                if ($debut < $ligne - 1) {
                                    $this->excelWriter->mergeCellsCaR(self::COL_UE, $debut, self::COL_UE, $ligne - 1);
                                    $this->excelWriter->mergeCellsCaR(self::COL_INTITULE_UE, $debut, self::COL_INTITULE_UE, $ligne - 1);

                                    // bordure fine

                                    $this->excelWriter->borderOutsiteInside(self::COL_UE, $debut, self::COL_INTITULE_UE, $ligne - 1);
                                    $this->excelWriter->borderOutsiteInside(self::COL_NUM_EC, $debut, self::COL_INTITULE_EC, $ligne - 1);
                                    $this->excelWriter->borderOutsiteInside(self::COL_INTITULE_EC_EN, $debut, self::COL_INTITULE_EC_EN, $ligne - 1);
                                    $this->excelWriter->borderOutsiteInside(self::COL_RESP_EC, $debut, self::COL_RESP_EC, $ligne - 1);
                                    $this->excelWriter->borderOutsiteInside(self::COL_LANGUE_EC, $debut, self::COL_LANGUE_EC, $ligne - 1);
                                    $this->excelWriter->borderOutsiteInside(self::COL_SUPPORT_ANGLAIS, $debut, self::COL_SUPPORT_ANGLAIS, $ligne - 1);
                                    $this->excelWriter->borderOutsiteInside(self::COL_TYPE_EC, $debut, self::COL_TYPE_EC, $ligne - 1);
                                    $this->excelWriter->borderOutsiteInside(self::COL_COURS_MUTUALISE, $debut, self::COL_COURS_MUTUALISE, $ligne - 1);
                                    $this->excelWriter->borderOutsiteInside(self::COL_COMPETENCES, $debut, self::COL_COMPETENCES, $ligne - 1);
                                    $this->excelWriter->borderOutsiteInside(self::COL_ECTS, $debut, self::COL_ECTS, $ligne - 1);
                                    $this->excelWriter->borderOutsiteInside(self::COL_HEURES_PRES_CM, $debut, self::COL_HEURES_PRES_TP, $ligne - 1);
                                    $this->excelWriter->borderOutsiteInside(self::COL_HEURES_PRES_TOTAL, $debut, self::COL_HEURES_PRES_TOTAL, $ligne - 1);
                                    $this->excelWriter->borderOutsiteInside(self::COL_HEURES_DIST_CM, $debut, self::COL_HEURES_DIST_TP, $ligne - 1);
                                    $this->excelWriter->borderOutsiteInside(self::COL_HEURES_DIST_TOTAL, $debut, self::COL_HEURES_DIST_TOTAL, $ligne - 1);
                                    $this->excelWriter->borderOutsiteInside(self::COL_HEURES_AUTONOMIE, $debut, self::COL_HEURES_AUTONOMIE, $ligne - 1);
                                    $this->excelWriter->borderOutsiteInside(self::COL_HEURES_TOTAL, $debut, self::COL_HEURES_TOTAL, $ligne - 1);

                                    $this->excelWriter->borderOutsiteInside(self::COL_MCCC_CCI, $debut, self::COL_MCCC_CCI, $ligne - 1);
                                    $this->excelWriter->borderOutsiteInside(self::COL_MCCC_CC, $debut, self::COL_MCCC_CC, $ligne - 1);
                                    $this->excelWriter->borderOutsiteInside(self::COL_MCCC_CT, $debut, self::COL_MCCC_CT, $ligne - 1);

                                    $this->excelWriter->borderOutsiteInside(self::COL_MCCC_SECONDE_CHANCE_CC_SUP_10, $debut, self::COL_MCCC_SECONDE_CHANCE_CC_SUP_10, $ligne - 1);

                                    //$this->excelWriter->borderOutsiteInside(self::COL_MCCC_SECONDE_CHANCE_CC_AVEC_TP, $debut, self::COL_MCCC_SECONDE_CHANCE_CC_AVEC_TP, $ligne - 1);
                                    $this->excelWriter->borderOutsiteInside(self::COL_MCCC_SECONDE_CHANCE_CT, $debut, self::COL_MCCC_SECONDE_CHANCE_CT, $ligne - 1);

                                    $this->excelWriter->borderOutsiteInside(self::COL_MCCC_SECONDE_CHANCE_CC_SANS_TP, $debut, self::COL_MCCC_SECONDE_CHANCE_CC_AVEC_TP, $ligne - 1);
                                }
                            }
                            $this->excelWriter->writeCellXY(self::COL_UE, $debut, $ue->display, ['wrap' => true, 'style' => 'HORIZONTAL_CENTER', 'font-weight' => false]);
                            $this->excelWriter->writeCellXY(self::COL_INTITULE_UE, $debut, $ue->ue->getLibelle(), ['wrap' => true]);
                        }
                        foreach ($ue->uesEnfants as $uee) {
                            $debut = $ligne;
                            if ($uee->ue->getNatureUeEc() !== null && $uee->ue->getNatureUeEc()->isLibre()) {
                                $ligne = $this->afficheUeLibre($ligne, $uee);
                            } else {
                                foreach ($uee->elementConstitutifs as $ec) {
                                    $ligne = $this->afficheEc($ligne, $ec);
                                    foreach ($ec->elementsConstitutifsEnfants as $ece) {
                                        $ligne = $this->afficheEc($ligne, $ece);
                                    }
                                }

                                if ($debut < $ligne - 1) {
                                    $this->excelWriter->mergeCellsCaR(self::COL_UE, $debut, self::COL_UE, $ligne - 1);
                                    $this->excelWriter->mergeCellsCaR(self::COL_INTITULE_UE, $debut, self::COL_INTITULE_UE, $ligne - 1);
                                }
                            }
                            $this->excelWriter->writeCellXY(self::COL_UE, $debut, $uee->display, ['wrap' => true, 'style' => 'HORIZONTAL_CENTER', 'font-weight' => false]);
                            $this->excelWriter->writeCellXY(self::COL_INTITULE_UE, $debut, $uee->ue->getLibelle(), ['wrap' => true]);
                        }
                    }
                    $ligne = $this->afficheSommeSemestre($ligne, $totalAnnee, $semestre);

                    $this->excelWriter->mergeCellsCaR(self::COL_SEMESTRE, $debutSemestre, self::COL_SEMESTRE, $ligne - 1);
                    $this->excelWriter->writeCellXY(self::COL_SEMESTRE, $debutSemestre, 'S' . $semestre->ordre);

                    $this->excelWriter->writeCellXY(self::COL_HEURES_PRES_CM, $ligne, $totalAnnee->totalCmPresentiel === 0.0 ? '' : $totalAnnee->totalCmPresentiel, ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_HEURES_PRES_TD, $ligne, $totalAnnee->totalTdPresentiel === 0.0 ? '' : $totalAnnee->totalTdPresentiel, ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_HEURES_PRES_TP, $ligne, $totalAnnee->totalTpPresentiel === 0.0 ? '' : $totalAnnee->totalTpPresentiel, ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_HEURES_PRES_TOTAL, $ligne, $totalAnnee->getTotalPresentiel() === 0.0 ? '' : $totalAnnee->getTotalPresentiel(), ['style' => 'HORIZONTAL_CENTER']);
                }
                $this->excelWriter->writeCellXY(
                    self::COL_HEURES_DIST_CM,
                    $ligne,
                    $totalAnnee->totalCmDistanciel === 0.0 ? '' : $totalAnnee->totalCmDistanciel,
                    ['style' => 'HORIZONTAL_CENTER']
                );
                $this->excelWriter->writeCellXY(
                    self::COL_HEURES_DIST_TD,
                    $ligne,
                    $totalAnnee->totalTdDistanciel === 0.0 ? '' : $totalAnnee->totalTdDistanciel,
                    ['style' => 'HORIZONTAL_CENTER']
                );
                $this->excelWriter->writeCellXY(
                    self::COL_HEURES_DIST_TP,
                    $ligne,
                    $totalAnnee->totalTpDistanciel === 0.0 ? '' : $totalAnnee->totalTpDistanciel,
                    ['style' => 'HORIZONTAL_CENTER']
                );
                $this->excelWriter->writeCellXY(
                    self::COL_HEURES_DIST_TOTAL,
                    $ligne,
                    $totalAnnee->getTotalDistanciel() === 0.0 ? '' : $totalAnnee->getTotalDistanciel(),
                    ['style' => 'HORIZONTAL_CENTER']
                );

                $this->excelWriter->writeCellXY(
                    self::COL_HEURES_TOTAL,
                    $ligne,
                    $totalAnnee->getVolumeTotal() === 0.0 ? '' : $totalAnnee->getVolumeTotal(),
                    ['style' => 'HORIZONTAL_CENTER']
                );

                $this->excelWriter->writeCellXY(
                    self::COL_HEURES_PRES_CM,
                    $ligne + 1,
                    $totalAnnee->getVolumeTotal() === 0.0 ? '' : $totalAnnee->getVolumeTotal(),
                    ['style' => 'HORIZONTAL_CENTER']
                );

                $this->excelWriter->writeCellXY(
                    self::COL_HEURES_PRES_CM,
                    $ligne + 3,
                    $totalFormation->sommeFormationTotalPresDist() === 0.0 ? '' : $totalFormation->sommeFormationTotalPresDist(),
                    ['style' => 'HORIZONTAL_CENTER']
                );

                $this->excelWriter->writeCellXY(
                    self::COL_HEURES_AUTONOMIE,
                    $ligne + 1,
                    $totalAnnee->getTotalVolumeTe() === 0.0 ? '' : $totalAnnee->getTotalVolumeTe(),
                    ['style' => 'HORIZONTAL_CENTER']
                );

                $this->excelWriter->writeCellXY(
                    self::COL_HEURES_PRES_CM,
                    $ligne + 2,
                    $totalAnnee->getTotalEtudiant() === 0.0 ? '' : $totalAnnee->getTotalEtudiant(),
                    ['style' => 'HORIZONTAL_CENTER']
                );
            }

            // couleur des lignes semestres
            foreach ($this->lignesSemestre as $ligneSemestre) {
                $this->excelWriter->setRangeStyle('B' . $ligneSemestre . ':AD' . $ligneSemestre, [
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'rotation' => 90,
                        'startColor' => [
                            'argb' => 'FFafafaf',
                        ],
                        'endColor' => [
                            'argb' => 'FFafafaf',
                        ],
                    ],]);
            }

            foreach ($this->lignesEcColorees as $lignesEcColoree) {
                $this->excelWriter->setRangeStyle('D' . $lignesEcColoree . ':AD' . $lignesEcColoree, [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'rotation' => 90,
                        'startColor' => [
                            'argb' => 'FFcecece',
                        ],
                        'endColor' => [
                            'argb' => 'FFcecece',
                        ],
                    ],]);
            }


            //suppression de la ligne modèle 18
            $this->excelWriter->removeRow(18);
            $this->updateIfNotFull();
            $this->excelWriter->setPrintArea('A1:AD' . $ligne + 7);
            $this->excelWriter->configSheet(
                ['zoom' => 60,
                    'topLeftCell' => 'A1']
            );
        }
        $this->genereReferentielCompetences($parcours, $formation);

        //supprimer la feuille de modèle
        $this->excelWriter->removeSheetByIndex(0);

        if ($formation->isHasParcours() === true) {
            $texte = $formation->gettypeDiplome()?->getLibelleCourt() . ' ' . $parcours->getLibelle();
        } else {
            $texte = $formation->gettypeDiplome()?->getLibelleCourt() . ' ' . $formation->getDisplay();
        }

        $this->fileName = Tools::FileName('MCCC - ' . $anneeUniversitaire->getLibelle() . ' - ' . $texte, 50);
    }

    public function exportExcelLicenceMccc(
        AnneeUniversitaire $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface $dateEdition = null,
        bool               $versionFull = true
    ): StreamedResponse {
        $this->genereExcelLicenceMccc($anneeUniversitaire, $parcours, $dateEdition, $versionFull);
        return $this->excelWriter->genereFichier($this->fileName);
    }

    public function exportPdfLicenceMccc(
        AnneeUniversitaire $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface $dateEdition = null,
        bool               $versionFull = true
    ): Response {
        $this->genereExcelLicenceMccc($anneeUniversitaire, $parcours, $dateEdition, $versionFull);

        $fichier = $this->excelWriter->saveFichier($this->fileName, $this->dir . '/temp/');

        $request = Gotenberg::libreOffice('http://localhost:3000')
            ->convert(Stream::path($fichier));

        $reponse = $this->client->sendRequest($request);

        if ($reponse) {
            unlink($this->dir . '/temp/'. $this->fileName . '.xlsx');
        }

        // retourner une réponse avec le contenu du PDF
        return new Response($reponse->getBody()->getContents(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $this->fileName . '.pdf"',
        ]);
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

    public function exportAndSavePdfLicenceMccc(
        AnneeUniversitaire $anneeUniversitaire,
        Parcours           $parcours,
        string             $dir,
        DateTimeInterface  $dateEdition,
        bool               $versionFull = true
    ): string {
        $this->genereExcelLicenceMccc($anneeUniversitaire, $parcours, $dateEdition, $versionFull);

        $fichier = $this->excelWriter->saveFichier($this->fileName, $dir);

        $request = Gotenberg::libreOffice('http://localhost:3000')
            ->outputFilename($this->fileName)
            ->convert(Stream::path($fichier));

        return Gotenberg::save($request, $dir);
    }

    public function getMcccs(StructureEc $structureEc): array
    {
        //todo: a mutualiser avec le code dans LicenceTypeDiplome
        $mcccs = $structureEc->mcccs;
        $tabMcccs = [];

        if ($structureEc->typeMccc === 'cci') {
            foreach ($mcccs as $mccc) {
                $tabMcccs[$mccc->getNumeroSession()] = $mccc;
            }
        } else {
            foreach ($mcccs as $mccc) {
                if ($mccc->isSecondeChance()) {
                    $tabMcccs[3]['chance'] = $mccc;
                } elseif ($mccc->isControleContinu() === true && $mccc->isExamenTerminal() === false) {
                    $tabMcccs[$mccc->getNumeroSession()]['cc'][$mccc->getNumeroEpreuve() ?? 1] = $mccc;
                } elseif ($mccc->isControleContinu() === false && $mccc->isExamenTerminal() === true) {
                    $tabMcccs[$mccc->getNumeroSession()]['et'][$mccc->getNumeroEpreuve() ?? 1] = $mccc;
                }
            }
        }

        return $tabMcccs;
    }

    private function genereReferentielCompetences(Parcours $parcours, Formation $formation): void
    {
        $modele = $this->excelWriter->getSheetByName(self::PAGE_REF_COMPETENCES);
        if ($modele === null) {
            throw new \Exception('Le modèle n\'existe pas');
        }

        //en-tête du fichier
        $modele->setCellValue(self::CEL_ANNEE_UNIVERSITAIRE, 'Année Universitaire ' . $formation->getAnneeUniversitaire()?->getLibelle());
        $modele->setCellValue('D5', $formation->getTypeDiplome()?->getLibelle());
        $modele->setCellValue('D6', $formation->getDisplay());
        $modele->setCellValue('D7', $parcours->isParcoursDefaut() === false ? $parcours->getLibelle() : '');
        $modele->setCellValue('D11', $formation->getComposantePorteuse()?->getLibelle());
        $modele->setCellValue('D13', $parcours->getLocalisation()?->getLibelle());

        $bccs = $parcours->getBlocCompetences();

        $ligne = 16;
        $this->excelWriter->setSheet($modele);
        foreach ($bccs as $bcc) {
            $this->excelWriter->writeCellXY(1, $ligne, $bcc->getCode(), ['font-weight' => 'bold', 'style' => 'HORIZONTAL_RIGHT']);
            $this->excelWriter->writeCellXY(2, $ligne, $bcc->getLibelle(), ['wrap' => true, 'font-weight' => 'bold']);
            $this->excelWriter->mergeCellsCaR(2, $ligne, 3, $ligne);
            $ligne++;
            foreach ($bcc->getCompetences() as $competence) {
                $this->excelWriter->writeCellXY(2, $ligne, $competence->getCode(), ['font-weight' => 'bold', 'style' => 'HORIZONTAL_RIGHT']);
                $this->excelWriter->writeCellXY(3, $ligne, $competence->getLibelle(), ['wrap' => true]);
                $this->excelWriter->getRowAutosize($ligne);
                $ligne++;
            }
        }

        $this->excelWriter->getColumnsAutoSize('D', 'D');
        $this->excelWriter->setPrintArea('A1:D' . $ligne);
    }

    private function afficheEc(int $ligne, StructureEc $structureEc): int
    {
        $ec = $structureEc->elementConstitutif;
        $this->excelWriter->insertNewRowBefore($ligne);
        $this->excelWriter->writeCellXY(self::COL_NUM_EC, $ligne, $ec->getCode());//todo: gérer les cas

        if ($ec->getNatureUeEc() !== null && $ec->getNatureUeEc()->isLibre() === true) {
            $this->excelWriter->writeCellXY(self::COL_INTITULE_EC, $ligne, $ec->getLibelle() . ' (EC à choix libre) ' . $ec->getTexteEcLibre(), ['wrap' => true]);
            $this->excelWriter->writeCellXY(self::COL_INTITULE_EC_EN, $ligne, '', ['wrap' => true]);
            $this->excelWriter->writeCellXY(self::COL_RESP_EC, $ligne, '', ['wrap' => true]);
            $this->lignesEcColorees[] = $ligne;
        } elseif ($ec->getNatureUeEc() !== null && $ec->getNatureUeEc()->isChoix() === true && $ec->getEcParent() === null) {
            $this->excelWriter->writeCellXY(self::COL_INTITULE_EC, $ligne, $ec->getLibelle() . ' (EC à choix restreint, choisir une parmis les choix ci-dessous)', ['wrap' => true]);
            $this->excelWriter->writeCellXY(self::COL_INTITULE_EC_EN, $ligne, '', ['wrap' => true]);
            $this->excelWriter->writeCellXY(self::COL_RESP_EC, $ligne, '', ['wrap' => true]);
            $this->lignesEcColorees[] = $ligne;
        } elseif ($ec->getFicheMatiere() !== null) {
            $this->excelWriter->writeCellXY(self::COL_INTITULE_EC, $ligne, $ec->getFicheMatiere()->getLibelle(), ['wrap' => true]);
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
            $texte = [];
            foreach ($structureEc->bccs as $comp) {
                $texte[] = $comp->getCode();
            }

            //suppression des doublons
            $texte = array_unique($texte);
            $texte = implode(', ', $texte);
            $this->excelWriter->writeCellXY(self::COL_COMPETENCES, $ligne, $texte);
        } else {
            $this->excelWriter->writeCellXY(self::COL_INTITULE_EC, $ligne, $ec->getTexteEcLibre(), ['wrap' => true]);
        }

        // MCCC
        $mcccs = $this->getMcccs($structureEc);

        switch ($structureEc->typeMccc) {
            case 'cc':
                $texte = '';
                $texteAvecTp = '';
                $hasTp = false;
                $pourcentageTp = 0;
                if (array_key_exists(1, $mcccs) && array_key_exists('cc', $mcccs[1])) {
                    $nb = 1;
                    $nb2 = 1;
                    foreach ($mcccs[1]['cc'] as $mccc) {
                        for ($i = 1; $i <= $mccc->getNbEpreuves(); $i++) {
                            $texte .= 'CC' . $nb . ' (' . $mccc->getPourcentage() . '%); ';
                            $nb++;
                        }

                        if ($mccc->hasTp()) {
                            $hasTp = true;
                            if ($mccc->getNbEpreuves() === 1) {
                                //si une seule épreuve de CC, pas de prise en compte du %de TP en seconde session
                                //todo: interdire la saisie d'un pourcentage de TP si une seule épreuve de CC
                                $pourcentageTp += $mccc->getPourcentage();
                                $texteAvecTp .= 'TPr' . $nb2 . ' (' . $mccc->getPourcentage() . '%); ';
                            } else {
                                $pourcentageTp += $mccc->pourcentageTp();
                                $texteAvecTp .= 'TPr' . $nb2 . ' (' . $mccc->getPourcentage() . '%); ';
                            }


                            $nb2++;
                        }
                    }

                    $texte = substr($texte, 0, -2);
                    $this->excelWriter->writeCellXY(self::COL_MCCC_CC, $ligne, $texte);
                }

                if (array_key_exists(2, $mcccs) && array_key_exists('et', $mcccs[2]) && is_array($mcccs[2]['et'])) {
                    $texte = '';
                    $pourcentageTpEt = $pourcentageTp / count($mcccs[2]['et']);
                    foreach ($mcccs[2]['et'] as $mccc) {
                        $texte .= $this->displayTypeEpreuveWithDureePourcentage($mccc);
                        $texteAvecTp .= $this->displayTypeEpreuveWithDureePourcentageTp($mccc, $pourcentageTpEt);
                    }

                    $texte = substr($texte, 0, -2);
                }

                if ($hasTp) {
                    $texteAvecTp = substr($texteAvecTp, 0, -2);
                    $this->excelWriter->writeCellXY(self::COL_MCCC_SECONDE_CHANCE_CC_AVEC_TP, $ligne, str_replace(';', '+', $texteAvecTp));
                } else {
                    $this->excelWriter->writeCellXY(self::COL_MCCC_SECONDE_CHANCE_CC_SANS_TP, $ligne, $texte);
                }

                break;
            case 'cci':
                $texte = '';
                foreach ($mcccs as $mccc) {
                    $texte .= 'CC' . $mccc->getNumeroSession() . ' (' . $mccc->getPourcentage() . '%); ';
                }
                $texte = substr($texte, 0, -2);
                $this->excelWriter->writeCellXY(self::COL_MCCC_CCI, $ligne, $texte);

                break;
            case 'cc_ct':
                if (array_key_exists(1, $mcccs) && array_key_exists('cc', $mcccs[1]) && $mcccs[1]['cc'] !== null) {
                    $texte = '';
                    foreach ($mcccs[1]['cc'] as $mccc) {
                        $texte .= 'CC ' . $mccc->getNbEpreuves() . ' épreuve(s) (' . $mccc->getPourcentage() . '%); ';
                    }
                    $texte = substr($texte, 0, -2);
                    $this->excelWriter->writeCellXY(self::COL_MCCC_CC, $ligne, $texte);
                }

                $texteAvecTp = '';
                $texteCc = '';
                $pourcentageTp = 0;
                $pourcentageCc = 0;
                $nb = 1;
                $hasTp = false;
                if (array_key_exists(1, $mcccs) && array_key_exists('cc', $mcccs[1])) {
                    foreach ($mcccs[1]['cc'] as $mccc) {
                        if ($mccc->hasTp()) {
                            $hasTp = true;
                            if ($mccc->getNbEpreuves() === 1) {
                                //si une seule épreuve de CC, pas de prise en compte du %de TP en seconde session
                                $pourcentageTp += $mccc->getPourcentage();
                            } else {
                                $pourcentageTp += $mccc->pourcentageTp();
                            }


                        }
                        $pourcentageCc += $mccc->getPourcentage();
                        $texteCc .= 'CC (' . $mccc->getPourcentage() . '%); ';
                    }

                    if ($hasTp) {
                        $texteAvecTp .= 'TPr (' . $pourcentageTp . '%); ';
                    }

                    if (array_key_exists('et', $mcccs[1]) && $mcccs[1]['et'] !== null) {
                        $texteEpreuve = '';
                        foreach ($mcccs[1]['et'] as $mccc) {
                            $texteEpreuve .= $this->displayTypeEpreuveWithDureePourcentage($mccc);
                        }

                        $texteEpreuve = substr($texteEpreuve, 0, -2);
                        $this->excelWriter->writeCellXY(self::COL_MCCC_CT, $ligne, $texteEpreuve);
                    }
                }

                if (array_key_exists(2, $mcccs) && array_key_exists('et', $mcccs[2]) && $mcccs[2]['et'] !== null) {
                    $texteEpreuve = '';
                    $pourcentageTpEt = $pourcentageTp / count($mcccs[2]['et']);
                    $pourcentageCcEt = $pourcentageCc / count($mcccs[2]['et']);
                    foreach ($mcccs[2]['et'] as $mccc) {
                        $texteEpreuve .= $this->displayTypeEpreuveWithDureePourcentage($mccc);
                        $texteAvecTp .= $this->displayTypeEpreuveWithDureePourcentageTp($mccc, $pourcentageTpEt);
                        $texteCc .= $this->displayTypeEpreuveWithDureePourcentageTp($mccc, $pourcentageCcEt);
                    }

                    $texteEpreuve = substr($texteEpreuve, 0, -2);
                    $texteCc = substr($texteCc, 0, -2);
                    $texteCc = str_replace('CC', 'CCr', $texteCc);
                    $texteAvecTp = substr($texteAvecTp, 0, -2);


                    if ($hasTp) {
                        $this->excelWriter->writeCellXY(self::COL_MCCC_SECONDE_CHANCE_CC_AVEC_TP, $ligne, str_replace(';', '+', $texteAvecTp));
                    } else {
                        //si TP cette celulle est vide...
                        $this->excelWriter->writeCellXY(self::COL_MCCC_SECONDE_CHANCE_CC_SANS_TP, $ligne, $texteEpreuve);
                    }
                    $this->excelWriter->writeCellXY(self::COL_MCCC_SECONDE_CHANCE_CC_SUP_10, $ligne, str_replace(';', '+', $texteCc));
                }

                //on garde CC et on complète avec le reste de pourcentage de l'ET


                break;
            case 'ct':
                $quitus = $ec->isQuitus();
                if (array_key_exists(1, $mcccs) && array_key_exists('et', $mcccs[1]) && $mcccs[1]['et'] !== null) {
                    $texteEpreuve = '';
                    foreach ($mcccs[1]['et'] as $mccc) {
                        $texteEpreuve .= $this->displayTypeEpreuveWithDureePourcentage($mccc, $quitus);
                    }

                    $texteEpreuve = substr($texteEpreuve, 0, -2);

                    $this->excelWriter->writeCellXY(self::COL_MCCC_CT, $ligne, $texteEpreuve);
                }

                if (array_key_exists(2, $mcccs) && array_key_exists('et', $mcccs[2]) && $mcccs[2]['et'] !== null) {
                    $texteEpreuve = '';
                    foreach ($mcccs[2]['et'] as $mccc) {
                        $texteEpreuve .= $this->displayTypeEpreuveWithDureePourcentage($mccc, $quitus);
                    }

                    $texteEpreuve = substr($texteEpreuve, 0, -2);
                    $this->excelWriter->writeCellXY(self::COL_MCCC_SECONDE_CHANCE_CT, $ligne, $texteEpreuve);
                }
                break;
        }


        $this->excelWriter->writeCellXY(self::COL_TYPE_EC, $ligne, $ec->getTypeEc() ? $ec->getTypeEc()->getLibelle() : '');

        // Heures
        $this->excelWriter->writeCellXY(self::COL_ECTS, $ligne, $structureEc->heuresEctsEc->ects === 0.0 ? '' : $structureEc->heuresEctsEc->ects);
        $this->excelWriter->writeCellXY(self::COL_HEURES_PRES_CM, $ligne, $structureEc->heuresEctsEc->cmPres === 0.0 ? '' : $structureEc->heuresEctsEc->cmPres);
        $this->excelWriter->writeCellXY(self::COL_HEURES_PRES_TD, $ligne, $structureEc->heuresEctsEc->tdPres === 0.0 ? '' : $structureEc->heuresEctsEc->tdPres);
        $this->excelWriter->writeCellXY(self::COL_HEURES_PRES_TP, $ligne, $structureEc->heuresEctsEc->tpPres === 0.0 ? '' : $structureEc->heuresEctsEc->tpPres);
        $this->excelWriter->writeCellXY(self::COL_HEURES_PRES_TOTAL, $ligne, $structureEc->heuresEctsEc->sommeEcTotalPres() === 0.0 ? '' : $structureEc->heuresEctsEc->sommeEcTotalPres());

        //si pas distanciel, griser...
        $this->excelWriter->writeCellXY(self::COL_HEURES_DIST_CM, $ligne, $structureEc->heuresEctsEc->cmDist === 0.0 ? '' : $structureEc->heuresEctsEc->cmDist);
        $this->excelWriter->writeCellXY(self::COL_HEURES_DIST_TD, $ligne, $structureEc->heuresEctsEc->tdDist === 0.0 ? '' : $structureEc->heuresEctsEc->tdDist);
        $this->excelWriter->writeCellXY(self::COL_HEURES_DIST_TP, $ligne, $structureEc->heuresEctsEc->tpDist === 0.0 ? '' : $structureEc->heuresEctsEc->tpDist);
        $this->excelWriter->writeCellXY(self::COL_HEURES_DIST_TOTAL, $ligne, $structureEc->heuresEctsEc->sommeEcTotalDist() === 0.0 ? '' : $structureEc->heuresEctsEc->sommeEcTotalDist());
        $this->excelWriter->writeCellXY(self::COL_HEURES_AUTONOMIE, $ligne, $structureEc->heuresEctsEc->tePres === 0.0 ? '' : $structureEc->heuresEctsEc->tePres);

        $this->excelWriter->writeCellXY(self::COL_HEURES_TOTAL, $ligne, $structureEc->heuresEctsEc->sommeEcTotalPresDist() === 0.0 ? '' : $structureEc->heuresEctsEc->sommeEcTotalPresDist());

        $ligne++;
        return $ligne;
    }

    private function afficheUeLibre(int $ligne, StructureUe $ue): int
    {
        $this->excelWriter->insertNewRowBefore($ligne);
        $this->excelWriter->writeCellXY(self::COL_INTITULE_EC, $ligne, $ue->ue->getDescriptionUeLibre(), ['wrap' => true]);

        $ligne++;
        return $ligne;
    }

    private function updateIfNotFull(): void
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

            $this->excelWriter->copyFromCellToCell('G5', 'N5');
            $this->excelWriter->copyFromCellToCell('J5', 'S5');
            $this->excelWriter->copyFromCellToCell('G6', 'N6');
            $this->excelWriter->copyFromCellToCell('J6', 'S6');
            $this->excelWriter->copyFromCellToCell('G7', 'N7');
            $this->excelWriter->copyFromCellToCell('J7', 'S7');
            $this->excelWriter->copyFromCellToCell('G9', 'N9');
            $this->excelWriter->copyFromCellToCell('J9', 'S9');
            $this->excelWriter->copyFromCellToCell('G11', 'N11');
            $this->excelWriter->copyFromCellToCell('J11', 'S11');
            $this->excelWriter->copyFromCellToCell('G13', 'N13');
            $this->excelWriter->copyFromCellToCell('J13', 'S13');

            $this->excelWriter->unMergeCells('D15:M16');
            foreach ($this->lignesSemestre as $ligneSemestre) {
                $ligneSemestre--;
                $this->excelWriter->unMergeCells('B' . $ligneSemestre . ':L' . $ligneSemestre);
            }
            //suppression des colonnes
            $this->excelWriter->removeColumn('F', 5);
            $this->excelWriter->mergeCells('D15:H16');
            $this->excelWriter->mergeCells('I5:L5');
            $this->excelWriter->mergeCells('I6:L6');
            $this->excelWriter->mergeCells('I7:L7');
            $this->excelWriter->mergeCells('I9:L9');
            $this->excelWriter->mergeCells('I11:L11');
            $this->excelWriter->mergeCells('I13:L13');

            $style = ['alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ]];

            $this->excelWriter->cellStyle('I5', $style);
            $this->excelWriter->cellStyle('I6', $style);
            $this->excelWriter->cellStyle('I7', $style);
            $this->excelWriter->cellStyle('I9', $style);
            $this->excelWriter->cellStyle('I11', $style);
            $this->excelWriter->cellStyle('I13', $style);

            $this->excelWriter->mergeCells('N5:Q5');
            $this->excelWriter->mergeCells('N6:Q6');
            $this->excelWriter->mergeCells('N7:Q7');
            $this->excelWriter->mergeCells('N9:Q9');
            $this->excelWriter->mergeCells('N11:Q11');
            $this->excelWriter->mergeCells('N13:Q13');

            foreach ($this->lignesSemestre as $ligneSemestre) {
                $ligneSemestre--;
                $this->excelWriter->mergeCells('B' . $ligneSemestre . ':G' . $ligneSemestre);
            }
        }
    }

    private function displayDuree(?DateTimeInterface $duree): string
    {
        if ($duree === null) {
            return '';
        }

        return $duree->format('H\hi');
    }

    private function displayTypeEpreuveWithDureePourcentage(Mccc $mccc, ?bool $quitus = false): string
    {
        $texte = '';
        foreach ($mccc->getTypeEpreuve() as $type) {
            if ($type !== "" && $this->typeEpreuves[$type] !== null) {
                if ($quitus === true) {
                    $texte .= 'QUITUS ' . $this->typeEpreuves[$type]->getSigle();
                } else {
                    $duree = '';
                    if ($this->typeEpreuves[$type]->isHasDuree() === true) {
                        $duree = ' ' . $this->displayDuree($mccc->getDuree());
                    }

                    $texte .= $this->typeEpreuves[$type]->getSigle() . $duree . ' (' . $mccc->getPourcentage() . '%); ';
                }
            } else {
                $texte .= 'erreur épreuve; ';
            }
        }

        return $texte;
    }

    private function displayTypeEpreuveWithDureePourcentageTp(Mccc $mccc, float $pourcentage): string
    {
        $texte = '';
        foreach ($mccc->getTypeEpreuve() as $type) {
            if ($type !== "" && $this->typeEpreuves[$type] !== null) {
                $duree = '';
                if ($this->typeEpreuves[$type]->isHasDuree() === true) {
                    $duree = ' ' . $this->displayDuree($mccc->getDuree());
                }
                if (($mccc->getPourcentage() - $pourcentage) > 0.0) {
                    $texte .= $this->typeEpreuves[$type]->getSigle() . $duree . ' (' . ($mccc->getPourcentage() - $pourcentage) . '%); ';
                }
            } else {
                $texte .= 'erreur épreuve; ';
            }
        }

        return $texte;
    }

    private function afficheSommeSemestre(int $ligne, TotalVolumeHeure $totalAnnee, StructureSemestre $semestre): int
    {
        $this->excelWriter->insertNewRowBefore($ligne);

        $this->excelWriter->mergeCellsCaR(self::COL_UE, $ligne, self::COL_COMPETENCES, $ligne);
        $this->excelWriter->mergeCellsCaR(self::COL_MCCC_CCI, $ligne, self::COL_MCCC_SECONDE_CHANCE_CT, $ligne);
        $this->excelWriter->writeCellXY(self::COL_UE, $ligne, 'Total semestre S' . $semestre->ordre . ' ', ['style' => 'HORIZONTAL_RIGHT']);
        //somme ECTS semestre
        $this->excelWriter->writeCellXY(self::COL_ECTS, $ligne, $semestre->heuresEctsSemestre->sommeSemestreEcts === 0.0 ? '' : $semestre->heuresEctsSemestre->sommeSemestreEcts, ['style' => 'HORIZONTAL_CENTER']);

        //ligne de somme du semestre
        $this->excelWriter->writeCellXY(self::COL_HEURES_PRES_CM, $ligne, $semestre->heuresEctsSemestre->sommeSemestreCmPres === 0.0 ? '' : $semestre->heuresEctsSemestre->sommeSemestreCmPres, ['style' => 'HORIZONTAL_CENTER']);
        $this->excelWriter->writeCellXY(self::COL_HEURES_PRES_TD, $ligne, $semestre->heuresEctsSemestre->sommeSemestreTdPres === 0.0 ? '' : $semestre->heuresEctsSemestre->sommeSemestreTdPres, ['style' => 'HORIZONTAL_CENTER']);
        $this->excelWriter->writeCellXY(self::COL_HEURES_PRES_TP, $ligne, $semestre->heuresEctsSemestre->sommeSemestreTpPres === 0.0 ? '' : $semestre->heuresEctsSemestre->sommeSemestreTpPres, ['style' => 'HORIZONTAL_CENTER']);
        $this->excelWriter->writeCellXY(self::COL_HEURES_PRES_TOTAL, $ligne, $semestre->heuresEctsSemestre->sommeSemestreTotalPres() === 0.0 ? '' : $semestre->heuresEctsSemestre->sommeSemestreTotalPres(), ['style' => 'HORIZONTAL_CENTER']);

        $this->excelWriter->writeCellXY(self::COL_HEURES_DIST_CM, $ligne, $semestre->heuresEctsSemestre->sommeSemestreCmDist === 0.0 ? '' : $semestre->heuresEctsSemestre->sommeSemestreCmDist, ['style' => 'HORIZONTAL_CENTER']);
        $this->excelWriter->writeCellXY(self::COL_HEURES_DIST_TD, $ligne, $semestre->heuresEctsSemestre->sommeSemestreTdDist === 0.0 ? '' : $semestre->heuresEctsSemestre->sommeSemestreTdDist, ['style' => 'HORIZONTAL_CENTER']);
        $this->excelWriter->writeCellXY(self::COL_HEURES_DIST_TP, $ligne, $semestre->heuresEctsSemestre->sommeSemestreTpDist === 0.0 ? '' : $semestre->heuresEctsSemestre->sommeSemestreTpDist, ['style' => 'HORIZONTAL_CENTER']);
        $this->excelWriter->writeCellXY(self::COL_HEURES_DIST_TOTAL, $ligne, $semestre->heuresEctsSemestre->sommeSemestreTotalDist() === 0.0 ? '' : $semestre->heuresEctsSemestre->sommeSemestreTotalDist(), ['style' => 'HORIZONTAL_CENTER']);

        $this->excelWriter->writeCellXY(self::COL_HEURES_TOTAL, $ligne, $semestre->heuresEctsSemestre->sommeSemestreTotalPresDist() === 0.0 ? '' : $semestre->heuresEctsSemestre->sommeSemestreTotalPresDist(), ['style' => 'HORIZONTAL_CENTER']);

        $this->excelWriter->writeCellXY(self::COL_HEURES_AUTONOMIE, $ligne, $semestre->heuresEctsSemestre->sommeSemestreTePres === 0.0 ? '' : $semestre->heuresEctsSemestre->sommeSemestreTePres, ['style' => 'HORIZONTAL_CENTER']);
        $this->lignesSemestre[] = $ligne;

        $ligne++;
        return $ligne;
    }

    private function generePdfLicenceMccc(AnneeUniversitaire $anneeUniversitaire, Parcours $parcours, DateTimeInterface $dateEdition, bool $versionFull)
    {
    }
}
