<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/Export/butMcccMccc.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 24/05/2023 16:14
 */

namespace App\TypeDiplome\Export;

use App\Classes\CalculButStructureParcours;
use App\Classes\Excel\ExcelWriter;
use App\DTO\DiffObject;
use App\Entity\CampagneCollecte;
use App\Entity\FicheMatiere;
use App\Entity\Parcours;
use App\Enums\RegimeInscriptionEnum;
use App\Repository\FicheMatiereRepository;
use App\Service\VersioningParcours;
use App\Service\VersioningStructure;
use App\Utils\Tools;
use DateTimeInterface;
use Gotenberg\Gotenberg;
use Gotenberg\Stream;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;

class ButMcccVersion
{
    // Pages
    public const PAGE_MODELE = 'modele';
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
    public const CEL_PARCOURS_ECTS = 'AF26';
    public const CEL_PARCOURS = 'A26';

    //Colonnes sur Modèles

    public const COL_CODE_ELEMENT = 1;
    public const COL_CODE_EC = 2;
    public const COL_INTITULE = 3;
    public const COL_VOL_ETUDIANT = 4;
    public const COL_CM = 5;
    public const COL_TD = 6;
    public const COL_TP = 7;
    public const COL_HEURE_AUTONOMIE = 8;
    public const COL_FIRST_UE = 39;
    public const CEL_DATE_CONSEIL = 'N32';
    public const CEL_DATE_CFVU = 'N34';
    private string $fileName;
    private Parcours $parcours;

    private string $dir;


    public function __construct(
        KernelInterface                  $kernel,
        protected ClientInterface        $client,
        protected CalculButStructureParcours $calculStructureParcours,
        protected VersioningParcours      $versioningParcours,
        protected FicheMatiereRepository $ficheMatiereRepository,
        protected ExcelWriter            $excelWriter,
    ) {
        $this->dir = $kernel->getProjectDir() . '/public';
    }


    /**
     * @throws Exception
     * @throws \Exception
     */
    public function genereExcelbutMccc(
        CampagneCollecte   $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface $dateCfvu = null,
        ?DateTimeInterface $dateConseil = null,
        bool               $versionFull = true
    ): bool
    {
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

        $formation = $parcours->getFormation();
        $this->parcours = $parcours;

        if (null === $formation) {
            throw new \Exception('La formation n\'existe pas');
        }

        $dto = $this->calculStructureParcours->calcul($parcours);//todo: devrait passer par le typeDiplome?

        // version
        if ($parcours->getParcoursOrigineCopie() !== null) {
            $structureDifferencesParcours = $this->versioningParcours->getStructureDifferencesBetweenParcoursAndLastVersion($parcours->getParcoursOrigineCopie());//todo: a traiter selon BUT ou pas ?
            if ($structureDifferencesParcours !== null) {
                $diffStructure = (new VersioningStructure($structureDifferencesParcours, $dto))->calculDiff(true);
            } else {
                return false;
            }
        }

        $this->excelWriter->createFromTemplate('Annexe_MCCC_BUT.xlsx');

        // Prépare le modèle avant de dupliquer
        $modele = $this->excelWriter->getSheetByName(self::PAGE_MODELE);

        if ($modele === null) {
            throw new \Exception('Le modèle n\'existe pas');
        }

        //récupération des données
        // récupération des semestres du parcours puis classement par année et par ordre
        $tabSemestres = [];
        $semestres = $parcours->getSemestreParcours();
        foreach ($semestres as $semParc) {
            if ($semParc->getSemestre()?->isNonDispense() === false) {
                if ($semParc->getSemestre()?->getSemestreRaccroche() !== null) {
                    $tabSemestres[$semParc->getOrdre()] = $semParc->getSemestre()?->getSemestreRaccroche();
                } else {
                    $tabSemestres[$semParc->getOrdre()] = $semParc;
                }
            }
        }

        //en-tête du fichier
        $modele->setCellValue(self::CEL_DOMAINE, $formation->getDomaine()?->getLibelle());
        $modele->setCellValue(self::CEL_COMPOSANTE, $formation->getComposantePorteuse()?->getLibelle());
        $modele->setCellValue(self::CEL_ANNEE_UNIVERSITAIRE, $anneeUniversitaire->getAnneeUniversitaire()?->getLibelle());
        $modele->setCellValue(self::CEL_INTITULE_FORMATION, $formation->getDisplay());

        if ($formation->isHasParcours() === false) {
            $modele->setCellValue(self::CEL_SITE_FORMATION, $formation->getLocalisationMention()[0]?->getLibelle());
        } else {
            $modele->setCellValue(self::CEL_SITE_FORMATION, $parcours->getLocalisation()?->getLibelle());
            $modele->setCellValue(self::CEL_INTITULE_PARCOURS, $parcours->getDisplay());
            $modele->setCellValue(self::CEL_PARCOURS_ECTS, $parcours->getDisplay());
            $modele->setCellValue(self::CEL_PARCOURS, $parcours->getDisplay());
        }

        // dates
        $modele->setCellValue(self::CEL_DATE_CONSEIL, $dateConseil?->format('d/m/Y'));
        $modele->setCellValue(self::CEL_DATE_CFVU, $dateCfvu?->format('d/m/Y'));

        if ($dateCfvu !== null) {
            //changer le pied de page.
            $modele->getHeaderFooter()
                ->setOddFooter(
                    '&L&B' . 'Document généré depuis ORéOF'.
                    '&C&B' . 'Document validé en CFVU le '. $dateCfvu->format('d/m/Y')
                    . '&R&B' . 'Université de Reims Champagne-Ardenne'
                );
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
            $totalHeuresAvant = ['CM' => 0, 'TD' => 0, 'TP' => 0, 'TE' => 0];
            $totalHeuresApres = ['CM' => 0, 'TD' => 0, 'TP' => 0, 'TE' => 0];


            $diffSemestre = $diffStructure['semestres'][$i];
            $tabColUes = [];
            $clonedWorksheet = clone $modele;
            $clonedWorksheet->setTitle('Semestre S' . $i);
            $this->excelWriter->addSheet($clonedWorksheet, $index);
            $index++;
            $semestreSheets[$i] = $clonedWorksheet;


            //remplissage de chaque année
            //ligne départ 18
            $ligne = 24;
            if (array_key_exists($i, $tabSemestres)) {
                $this->excelWriter->setSheet($clonedWorksheet);
                $this->excelWriter->writeCellName(self::CEL_SEMESTRE_ETUDE, 'Semestre S' . $i);

                $colUe = self::COL_FIRST_UE;

                if ($tabSemestres[$i]->getSemestre()->getSemestreRaccroche() !== null) {
                    $semestre = $tabSemestres[$i]->getSemestre()->getSemestreRaccroche()->getSemestre();
                } else {
                    $semestre = $tabSemestres[$i]->getSemestre();
                }
                $tabFichesRessources = [];
                $tabFichesSaes = [];
                $tabFichesUes = [];
                // Affichage des UE + gestion des colonnes
                foreach ($semestre->getUes() as $ue) {
                    if ($ue->getUeRaccrochee() !== null) {
                        $ue = $ue->getUeRaccrochee()->getUe();
                    }

                    $diffUe = $diffSemestre['ues'][$ue->getOrdre()];

                    $totalCoeffAvant[$ue->getOrdre()] = ['Ressource' => 0, 'Sae' => 0];
                    $totalCoeffApres[$ue->getOrdre()] = ['Ressource' => 0, 'Sae' => 0];

                    foreach ($ue->getElementConstitutifs() as $keyEc => $ec) {
                        $fiche = $ec->getFicheMatiere();
                        $diffFiche = $diffUe['elementConstitutifs'][$keyEc];

                        if ($fiche !== null) {
                            if ($fiche->getTypeMatiere() === FicheMatiere::TYPE_MATIERE_RESSOURCE) {
                                $tabFichesRessources[$fiche->getSigle()]['ec'] = $ec;
                                $tabFichesRessources[$fiche->getSigle()]['diff'] = $diffFiche;
                                $tabFichesUes[$fiche->getSigle()][$ue->getOrdre()] = $diffFiche['heuresEctsEc']['ects'];
                                $totalCoeffAvant[$ue->getOrdre()]['Ressource'] += $diffFiche['heuresEctsEc']['ects']->getOriginalFloat();
                                $totalCoeffApres[$ue->getOrdre()]['Ressource'] += $diffFiche['heuresEctsEc']['ects']->getNewFloat();
                            }

                            if ($fiche->getTypeMatiere() === FicheMatiere::TYPE_MATIERE_SAE) {
                                $tabFichesSaes[$fiche->getSigle()]['ec'] = $ec;
                                $tabFichesSaes[$fiche->getSigle()]['diff'] = $diffFiche;
                                $tabFichesUes[$fiche->getSigle()][$ue->getOrdre()] = $diffFiche['heuresEctsEc']['ects'];
                                $totalCoeffAvant[$ue->getOrdre()]['Sae'] += $diffFiche['heuresEctsEc']['ects']->getOriginalFloat();
                                $totalCoeffApres[$ue->getOrdre()]['Sae'] += $diffFiche['heuresEctsEc']['ects']->getNewFloat();
                            }
                        }
                    }

                    $tabColUes[$ue->getOrdre()] = $colUe;
                    $this->excelWriter->writeCellXY($colUe, 18, 'BC' . $ue->getOrdre(), ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY($colUe, 19, $ue->getLibelle(), ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->mergeCellsCaR($colUe, 19, $colUe, 22);
                    $colUe++;
                    $this->excelWriter->insertNewColumnBefore($colUe);
                }

                //supprimer les cols en trop ?
                $this->excelWriter->removeColumn($colUe, 2);
                $this->excelWriter->mergeCellsCaR(self::COL_FIRST_UE, 17, $colUe - 1, 17);
                $this->excelWriter->cellStyle('AM17', ['alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]]);

                ksort($tabFichesRessources);
                ksort($tabFichesSaes);

                foreach ($tabFichesRessources as $ec) {
                    $fiche = $ec['ec']->getFicheMatiere();
                    $this->excelWriter->insertNewRowBefore($ligne);
                    $this->excelWriter->writeCellXY(self::COL_CODE_ELEMENT, $ligne, '', ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_CODE_EC, $ligne, $fiche->getSigle(), ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXYDiff(self::COL_INTITULE, $ligne, $ec['diff']['libelle'], ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXYDiff(self::COL_VOL_ETUDIANT, $ligne, $ec['diff']['heuresEctsEc']['sommeEcTotalPres'], ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXYDiff(self::COL_CM, $ligne, $ec['diff']['heuresEctsEc']['cmPres'], ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXYDiff(self::COL_TD, $ligne, $ec['diff']['heuresEctsEc']['tdPres'], ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXYDiff(self::COL_TP, $ligne, $ec['diff']['heuresEctsEc']['tpPres'], ['style' => 'HORIZONTAL_CENTER']);

                    //MCCC
                    $this->writeMccc($ec, $tabColonnes, $ligne);
                    $this->writeAcUe($fiche, $ligne, $tabColUes, $tabFichesUes);

                    $totalHeuresAvant['CM'] += $ec['diff']['heuresEctsEc']['cmPres']->getOriginalFloat();
                    $totalHeuresAvant['TD'] += $ec['diff']['heuresEctsEc']['tdPres']->getOriginalFloat();
                    $totalHeuresAvant['TP'] += $ec['diff']['heuresEctsEc']['tpPres']->getOriginalFloat();

                    $totalHeuresApres['CM'] += $ec['diff']['heuresEctsEc']['cmPres']->getNewFloat();
                    $totalHeuresApres['TD'] += $ec['diff']['heuresEctsEc']['tdPres']->getNewFloat();
                    $totalHeuresApres['TP'] += $ec['diff']['heuresEctsEc']['tpPres']->getNewFloat();


                    $ligne++;
                }

                $finRessource = $ligne - 1;

                $this->excelWriter->insertNewRowBefore($ligne);
                $ligne++;
                $debutSae = $ligne;

                foreach ($tabFichesSaes as $ec) {
                    $fiche = $ec['ec']->getFicheMatiere();
                    $this->excelWriter->insertNewRowBefore($ligne);
                    $this->excelWriter->writeCellXY(self::COL_CODE_ELEMENT, $ligne, '', ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXY(self::COL_CODE_EC, $ligne, $fiche->getSigle(), ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXYDiff(self::COL_INTITULE, $ligne, $ec['diff']['libelle'], ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXYDiff(self::COL_VOL_ETUDIANT, $ligne, $ec['diff']['heuresEctsEc']['sommeEcTotalPres'], ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXYDiff(self::COL_CM, $ligne, $ec['diff']['heuresEctsEc']['cmPres'] , ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXYDiff(self::COL_TD, $ligne, $ec['diff']['heuresEctsEc']['tdPres'], ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXYDiff(self::COL_TP, $ligne, $ec['diff']['heuresEctsEc']['tpPres'], ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXYDiff(self::COL_HEURE_AUTONOMIE, $ligne, $ec['diff']['heuresEctsEc']['tePres']);

                    //MCCC
                    $this->writeMccc($ec, $tabColonnes, $ligne);
                    $this->writeAcUe($fiche, $ligne, $tabColUes, $tabFichesUes);

                    $totalHeuresAvant['CM'] += $ec['diff']['heuresEctsEc']['cmPres']->getOriginalFloat();
                    $totalHeuresAvant['TD'] += $ec['diff']['heuresEctsEc']['tdPres']->getOriginalFloat();
                    $totalHeuresAvant['TP'] += $ec['diff']['heuresEctsEc']['tpPres']->getOriginalFloat();
                    $totalHeuresAvant['TE'] += $ec['diff']['heuresEctsEc']['tePres']->getOriginalFloat();

                    $totalHeuresApres['CM'] += $ec['diff']['heuresEctsEc']['cmPres']->getNewFloat();
                    $totalHeuresApres['TD'] += $ec['diff']['heuresEctsEc']['tdPres']->getNewFloat();
                    $totalHeuresApres['TP'] += $ec['diff']['heuresEctsEc']['tpPres']->getNewFloat();
                    $totalHeuresApres['TE'] += $ec['diff']['heuresEctsEc']['tePres']->getNewFloat();

                    $ligne++;
                }

                $this->excelWriter->colorCells('L' . $debutSae . ':W' . ($ligne - 1), 'FFCCCCCC');
                $this->excelWriter->colorCells('X23:AK' . $finRessource, 'FFCCCCCC');
                $this->excelWriter->colorCells('H23:H' . $finRessource, 'FFCCCCCC');
                $a = $ligne + 4;
                $b = $ligne + 3;
                foreach ($tabColUes as $keyUe => $colUe) {
                    $diffUe = $diffSemestre['ues'][$keyUe];
                    $lettreCol = Coordinate::stringFromColumnIndex($colUe);
                    //pour chaque colonne d'uE on met à jour la somme des ECTS dans la formule
                    $sommeEctsAvant = $totalCoeffAvant[$keyUe]['Ressource'] + $totalCoeffAvant[$keyUe]['Sae'];
                    $sommeEctsApres = $totalCoeffApres[$keyUe]['Ressource'] + $totalCoeffApres[$keyUe]['Sae'];


                    $this->excelWriter->writeCellXYDiff($colUe, $ligne + 2, $diffUe['heuresEctsUe']['sommeUeEcts'], ['style' => 'HORIZONTAL_CENTER']);
                    //somme des coeffs
                    $this->excelWriter->writeCellXYDiff($colUe, $ligne + 3, new DiffObject($sommeEctsAvant, $sommeEctsApres), ['style' => 'HORIZONTAL_CENTER']);
                    $this->excelWriter->writeCellXYDiff($colUe, $ligne + 4, new DiffObject($totalCoeffAvant[$keyUe]['Sae'], $totalCoeffApres[$keyUe]['Sae']), ['style' => 'HORIZONTAL_CENTER']);

                    $pourcentageSaeAvant = $sommeEctsAvant > 0 ? round($totalCoeffAvant[$keyUe]['Sae'] / $sommeEctsAvant * 100, 2) : 0;
                    $pourcentageSaeApres = $sommeEctsApres > 0 ? round($totalCoeffApres[$keyUe]['Sae'] / $sommeEctsApres * 100, 2) : 0;

                    $this->excelWriter->writeCellXYDiff($colUe, $ligne + 5, new DiffObject($pourcentageSaeAvant, $pourcentageSaeApres), ['style' => 'numerique']);
                }

                // affichage des "totaux"
                $this->excelWriter->writeCellXYDiff(self::COL_CM, $ligne + 2, new DiffObject($totalHeuresAvant['CM'], $totalHeuresApres['CM']), ['style' => 'HORIZONTAL_CENTER']);
                $this->excelWriter->writeCellXYDiff(self::COL_TD, $ligne + 2,
                    new DiffObject($totalHeuresAvant['TD'], $totalHeuresApres['TD']), ['style' => 'HORIZONTAL_CENTER']);
                $this->excelWriter->writeCellXYDiff(self::COL_TP, $ligne + 2, new DiffObject($totalHeuresAvant['TP'], $totalHeuresApres['TP']), ['style' => 'HORIZONTAL_CENTER']);
                $this->excelWriter->writeCellXYDiff(self::COL_HEURE_AUTONOMIE, $ligne + 2, new DiffObject($totalHeuresAvant['TE'], $totalHeuresApres['TE']), ['style' => 'HORIZONTAL_CENTER']);

                $sommeAvant = $totalHeuresAvant['CM'] + $totalHeuresAvant['TD'] + $totalHeuresAvant['TP'];
                $sommeApres = $totalHeuresApres['CM'] + $totalHeuresApres['TD'] + $totalHeuresApres['TP'];

                $this->excelWriter->writeCellXYDiff(self::COL_CM, $ligne + 3, new DiffObject($sommeAvant, $sommeApres), ['style' => 'HORIZONTAL_CENTER']);
            }

            //suppression de la ligne modèle 18
            $this->excelWriter->removeRow(23);
        }

        //supprimer la feuille de modèle
        $this->excelWriter->removeSheetByIndex(0);
        $this->excelWriter->setActiveSheetIndex(0);
        $this->excelWriter->setSelectedCells('A1');

        $this->fileName = Tools::FileName('MCCC - ' . $anneeUniversitaire->getLibelle() . ' - ' . $formation->gettypeDiplome()?->getLibelleCourt() . ' ' . $formation->getSigle(). ' ' . $parcours->getSigle(), 40);

        return true;
    }

    public function exportExcelbutMccc(
        CampagneCollecte   $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface $dateCfvu = null,
        ?DateTimeInterface $dateConseil = null,
        bool               $versionFull = true
    ): StreamedResponse {
        $this->genereExcelbutMccc($anneeUniversitaire, $parcours, $dateCfvu, $dateConseil, $versionFull);
        return $this->excelWriter->genereFichier($this->fileName);
    }

    public function exportPdfbutMccc(
        CampagneCollecte   $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface $dateCfvu = null,
        ?DateTimeInterface $dateConseil = null,
        bool               $versionFull = true
    ): Response {
        $this->genereExcelbutMccc($anneeUniversitaire, $parcours, $dateCfvu, $dateConseil, $versionFull);

        $fichier = $this->excelWriter->saveFichier($this->fileName, $this->dir . '/temp/');

        $request = Gotenberg::libreOffice('http://localhost:3000')
            ->convert(Stream::path($fichier));

        $reponse = $this->client->sendRequest($request);

        // retourner une réponse avec le contenu du PDF
        return new Response($reponse->getBody()->getContents(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $this->fileName . '.pdf"',
        ]);
    }

    public function exportAndSaveExcelbutMccc(
        CampagneCollecte   $anneeUniversitaire,
        Parcours           $parcours,
        string             $dir,
        ?DateTimeInterface $dateCfvu = null,
        ?DateTimeInterface $dateConseil = null,
        bool               $versionFull = true
    ): string {
        $this->genereExcelbutMccc($anneeUniversitaire, $parcours, $dateCfvu, $dateConseil, $versionFull);
        $this->excelWriter->saveFichier($this->fileName, $dir);
        return $this->fileName . '.xlsx';
    }

    private function writeMccc(mixed $ec, array $tabColonnes, int $ligne): void
    {
        $mcccs = $ec['ec']->getFicheMatiere()->getMcccs();
        $diffMccc = $ec['diff']['mcccs'];
        foreach ($mcccs as $mccc) {
            if ($mccc->getLibelle() !== '' && array_key_exists($mccc->getLibelle(), $tabColonnes)) {
                $this->excelWriter->writeCellXYDiff(
                    //convertir chiffre en lettre excel
                    Coordinate::columnIndexFromString($tabColonnes[$mccc->getLibelle()]['pourcentage']),
                    $ligne,
                    $diffMccc[$mccc->getCleUnique()]['pourcentage'] ?? null,
                    ['style' => 'HORIZONTAL_CENTER']
                );
                $this->excelWriter->writeCellXYDiff(
                    Coordinate::columnIndexFromString($tabColonnes[$mccc->getLibelle()]['nombre']),
                    $ligne,
                    $diffMccc[$mccc->getCleUnique()]['nbEpreuves'] ?? null,
                    ['style' => 'HORIZONTAL_CENTER']
                );
            }
        }
    }

    private function writeAcUe(FicheMatiere $fiche, int $ligne, array $tabColUes, array $tabFichesUes)
    {
        if (array_key_exists($fiche->getSigle(), $tabFichesUes)) {
            foreach ($tabFichesUes[$fiche->getSigle()] as $ueId => $ects) {
                if (array_key_exists($ueId, $tabColUes)) {
                    $this->excelWriter->writeCellXYDiff(
                        $tabColUes[$ueId],
                        $ligne,
                        $ects,
                        ['style' => 'HORIZONTAL_CENTER']
                    );
                }
            }
        }
    }
}
