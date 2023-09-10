<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/Source/LicenceTypeDiplome.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\TypeDiplome\Source;

use App\Entity\AnneeUniversitaire;
use App\Entity\ElementConstitutif;
use App\Entity\Mccc;
use App\Entity\Parcours;
use App\Repository\TypeEpreuveRepository;
use App\TypeDiplome\Export\LicenceMccc;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\Tools;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LicenceTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'licence';
    public const TEMPLATE_FORM_MCCC = 'licence.html.twig';
    public const NB_ANNEE = 3;

    public string $libelle = 'Licence';
    public array $typeEpreuves = [];

    public function __construct(
        TypeEpreuveRepository  $typeEpreuveRepository,
        EntityManagerInterface $entityManager,
        TypeDiplomeRegistry    $typeDiplomeRegistry,
        protected LicenceMccc  $licenceMccc
    )
    {
        parent::__construct($entityManager, $typeDiplomeRegistry);
        $this->typeEpreuves = [];
        $tyeE = $typeEpreuveRepository->findAll();
        foreach ($tyeE as $typeEpreuve) {
            $this->typeEpreuves[$typeEpreuve->getId()] = $typeEpreuve;
        }
    }

    public function exportExcelMccc(
        AnneeUniversitaire $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface $dateEdition = null,
        bool               $versionFull = true
    ): StreamedResponse
    {
        //todo: exploiter la date...
        return $this->licenceMccc->exportExcelLicenceMccc($anneeUniversitaire, $parcours, $dateEdition, $versionFull);
    }

    public function exportPdfMccc(
        AnneeUniversitaire $anneeUniversitaire,
        Parcours           $parcours,
        ?DateTimeInterface $dateEdition = null,
        bool               $versionFull = true
    ): StreamedResponse
    {
        //todo: exploiter la date...
        return $this->licenceMccc->exportPdfLicenceMccc($anneeUniversitaire, $parcours, $dateEdition, $versionFull);
    }

    public function exportAndSaveExcelMccc(
        string             $dir,
        AnneeUniversitaire $anneeUniversitaire,
        Parcours           $parcours,
        DateTimeInterface  $dateEdition,
        bool               $versionFull = true
    ): string
    {
        return $this->licenceMccc->exportAndSaveExcelLicenceMccc($anneeUniversitaire, $parcours, $dir, $dateEdition, $versionFull);
    }

    public function saveMcccs(ElementConstitutif $elementConstitutif, InputBag $request): void
    {
        $mcccs = $this->getMcccs($elementConstitutif);
        $typeD = 'L';

        switch ($request->get('choix_type_mccc')) {
            case 'cc':
                if ($request->get('type_cc') === 'cc') {
                    if (isset($mcccs[1]) && isset($mcccs[1]['cc']) && isset($mcccs[1]['cc'][1])) {
                        $mcccs[1]['cc'][1]->setPourcentage(50);
                        $mcccs[1]['cc'][1]->setNbEpreuves(2);
                    } else {
                        $mccc = new Mccc();
                        $mccc->setEc($elementConstitutif);
                        $mccc->setLibelle('Contrôle continu');
                        $mccc->setPourcentage(50);
                        $mccc->setNbEpreuves(2);
                        $mccc->setNumeroSession(1);
                        $mccc->setControleContinu(true);
                        $mccc->setExamenTerminal(false);
                        $mccc->setNumeroEpreuve(1);
                        $mcccs[1]['cc'][1] = $mccc;
                        $this->entityManager->persist($mccc);
                    }

                    if ($request->has('cc_has_tp') && $request->get('cc_has_tp') === 'on') {
                        $tab = [
                            'pourcentage' => 50,
                            'cc_has_tp' => 'on'
                        ];
                    } else {
                        $tab = [
                            'pourcentage' => 0,
                            'cc_has_tp' => false
                        ];
                    }
                    $mcccs[1]['cc'][1]->setOptions($tab);
                    $mcccs = $this->sauvegardeCts($elementConstitutif, $mcccs, $request->all(), 2, 's2_ct');
                } else {
                    $typeD = 'NOT-L';
                    // CC des autres diplômes //todo: a déplacer dans les autres types...
                    $mcccs = $this->sauvegardeCc($elementConstitutif, $mcccs, $request->all(), 1, 's1_cc', 'NOT-L');
                    $mcccs = $this->sauvegardeCts($elementConstitutif, $mcccs, $request->all(), 2, 's2_ct');
                }
                $etatMccc = $this->verificationMccc($elementConstitutif, $mcccs, $typeD);

                break;
            case 'cci':
                $pourcentages = $request->all()['pourcentage'];
                $totPourcentage = 0.0;
                //supprimer les MCCC en trop
                foreach ($mcccs as $mccc) {
                    if (!array_key_exists($mccc->getNumeroSession(), $pourcentages)) {
                        $this->entityManager->remove($mccc);
                    }
                }

                $this->entityManager->flush();
                foreach ($pourcentages as $key => $pourcentage) {
                    if (array_key_exists($key, $mcccs)) {
                        $mcccs[$key]->setPourcentage((float)$pourcentage);
                        $totPourcentage += (float)$pourcentage;
                    } else {
                        $mccc = new Mccc();
                        $mccc->setEc($elementConstitutif);
                        $mccc->setLibelle('Contrôle continu intégral ' . $key);
                        $mccc->setPourcentage((float)$pourcentage);
                        $mccc->setNbEpreuves(1);
                        $mccc->setNumeroSession((int)$key);
                        $mccc->setNumeroEpreuve((int)$key);
                        $mccc->setControleContinu(true);
                        $mccc->setExamenTerminal(false);
                        $mcccs[$key] = $mccc;
                        $totPourcentage += (float)$pourcentage;
                        $this->entityManager->persist($mccc);
                    }
                }
                $etatMccc = $totPourcentage === 100.0;
                break;
            case 'cc_ct':
                $tab = [
                    'pourcentage' => $request->get('cc_has_tp_pourcentage', 0),
                    'cc_has_tp' => $request->get('cc_has_tp', false)
                ];
                //cas classique
                if (array_key_exists(1, $mcccs) && array_key_exists('cc', $mcccs[1])) {
                    $mcccs[1]['cc'][1]->setPourcentage((float)$request->get('pourcentage_s1_cc'));
                    $mcccs[1]['cc'][1]->setNbEpreuves((int)$request->get('nbepreuve_s1_cc'));
                    $mcccs[1]['cc'][1]->setOptions($tab);
                } else {
                    $mccc = new Mccc();
                    $mccc->setEc($elementConstitutif);
                    $mccc->setLibelle('Contrôle continu');
                    $mccc->setControleContinu(true);
                    $mccc->setExamenTerminal(false);
                    $mccc->setNumeroSession(1);
                    $mccc->setNumeroEpreuve(1);
                    $mccc->setNbEpreuves((int)$request->get('nbepreuve_s1_cc'));
                    $mccc->setPourcentage((float)$request->get('pourcentage_s1_cc'));
                    $mccc->setOptions($tab);
                    $this->entityManager->persist($mccc);
                    $mcccs[1]['cc'][1] = $mccc;
                }

                $data = $request->all();

                $mcccs = $this->sauvegardeCts($elementConstitutif, $mcccs, $data, 1, 's1_ct', false);
                $mcccs = $this->sauvegardeCts($elementConstitutif, $mcccs, $data, 2, 's2_ct');
                $etatMccc = $this->verificationMccc($elementConstitutif, $mcccs, $typeD);

                //todo: encart sur vérification parcours/formation sur la structure selon le type, cas des licences. Permet de déporter les tests spécifiques dans les type de diplômes ou départer la vérification structure dans les types de diplômes
                break;
            case 'ct':
                //todo: gérer la durée obligatoire dans le contrôle MCCC...
                //gérer la suppression d'une entrée déjà dans la BDD...
                $data = $request->all();
                //récupérer toutes les clés commencant par typeEpreuve_s1_ct
                $mcccs = $this->sauvegardeCts($elementConstitutif, $mcccs, $data, 1, 's1_ct');
                $mcccs = $this->sauvegardeCts($elementConstitutif, $mcccs, $data, 2, 's2_ct');
                $etatMccc = $this->verificationMccc($elementConstitutif, $mcccs, $typeD);
                break;
        }


        $elementConstitutif->setEtatMccc($etatMccc ? 'Complet' : 'A Saisir');
        $this->entityManager->flush();
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
                    $tabMcccs[$mccc->getNumeroSession()]['cc'][$mccc->getNumeroEpreuve() ?? 1] = $mccc;
                } elseif ($mccc->isControleContinu() === false && $mccc->isExamenTerminal() === true) {
                    $tabMcccs[$mccc->getNumeroSession()]['et'][$mccc->getNumeroEpreuve() ?? 1] = $mccc;
                }
            }
        }

        return $tabMcccs;
    }

    public function clearMcccs(ElementConstitutif $elementConstitutif): void
    {
        $mcccs = $elementConstitutif->getMcccs();
        foreach ($mcccs as $mccc) {
            $this->entityManager->remove($mccc);
        }
        $this->entityManager->flush();
    }

    private function sauvegardeCts(ElementConstitutif $elementConstitutif, array $mcccs, array $data, int $session, string $cle, bool $fixToCent = true): array
    {
        $typeEpreuve_s1_ct = [];
        foreach ($data as $key => $value) {
            if (preg_match('/^typeEpreuve_' . $cle . '/', $key)) {
                $parts = explode('_', $key);
                $lastPart = $parts[count($parts) - 1];
                //retirer les deux premiers caractères
                $lastPart = substr($lastPart, 2);

                // Récupérer le code et le numéro
                $typeEpreuve_s1_ct[] = (int)$lastPart;
            }
        }

        foreach ($typeEpreuve_s1_ct as $numEp) {
            if (isset($mcccs[$session]) && isset($mcccs[$session]['et']) && isset($mcccs[$session]['et'][$numEp])) {
                if (array_key_exists('typeEpreuve_' . $cle . $numEp, $data) && $data['typeEpreuve_' . $cle . $numEp] === "") {
                    $mcccs[$session]['et'][$numEp]->setTypeEpreuve(null);
                } else {
                    $mcccs[$session]['et'][$numEp]->setTypeEpreuve([$data['typeEpreuve_' . $cle . $numEp]]);
                }

                $mcccs[$session]['et'][$numEp]->setNumeroEpreuve($numEp);
            } else {
                //n'existe pas à créer.
                $mccc = new Mccc();
                $mccc->setEc($elementConstitutif);
                $mccc->setLibelle('Contrôle terminal');
                $mccc->setControleContinu(false);
                $mccc->setExamenTerminal(true);
                $mccc->setNumeroSession($session);
                $mccc->setNbEpreuves(1);
                $mccc->setNumeroEpreuve($numEp);
                $mccc->setTypeEpreuve([$data['typeEpreuve_' . $cle . $numEp]]);


                $this->entityManager->persist($mccc);
                $mcccs[$session]['et'][$numEp] = $mccc;
            }

            if (count($typeEpreuve_s1_ct) === 1 && $fixToCent === true) {
                $mcccs[$session]['et'][$numEp]->setPourcentage(100);
            } else {

                if (array_key_exists('pourcentage_' . $cle . $numEp, $data) && $data['pourcentage_' . $cle . $numEp] !== '') {
                    $mcccs[$session]['et'][$numEp]->setPourcentage(Tools::convertToFloat($data['pourcentage_' . $cle . $numEp]));
                } else {
                    $mcccs[$session]['et'][$numEp]->setPourcentage(null);
                }

            }

            if (array_key_exists('duree_' . $cle . $numEp, $data) && $data['duree_' . $cle . $numEp] !== '') {
                $mcccs[$session]['et'][$numEp]->setDuree(Tools::convertToTime($data['duree_' . $cle . $numEp]));
            } else {
                $mcccs[$session]['et'][$numEp]->setDuree(null);
            }
        }

        //suppression des MCCC
        foreach ($mcccs[$session]['et'] as $mccc) {
            if (!in_array($mccc->getNumeroEpreuve(), $typeEpreuve_s1_ct)) {
                $this->entityManager->remove($mccc);
            }
        }

        return $mcccs;
    }

    private function sauvegardeCc(ElementConstitutif $elementConstitutif, array $mcccs, array $data, int $session, string $cle, string $typeD = 'L'): array
    {
        $typeEpreuve_s1_cc = [];
        foreach ($data as $key => $value) {
            if (preg_match('/^pourcentage_' . $cle . '/', $key)) {
                $parts = explode('_', $key);
                $lastPart = $parts[count($parts) - 1];
                //retirer les deux premiers caractères
                $lastPart = substr($lastPart, 2);

                // Récupérer le code et le numéro
                $typeEpreuve_s1_cc[] = (int)$lastPart;
            }
        }

        foreach ($typeEpreuve_s1_cc as $numEp) {
            if (isset($mcccs[$session]) && isset($mcccs[$session]['cc']) && isset($mcccs[$session]['cc'][$numEp])) {
                if ($typeD === 'L') {
                    $mcccs[$session]['cc'][$numEp]->setNbEpreuves(2);
                } else {
                    $mcccs[$session]['cc'][$numEp]->setNbEpreuves(1);
                }

                if (count($typeEpreuve_s1_cc) > 1) {
                    if (array_key_exists('pourcentage_' . $cle . $numEp, $data) && $data['pourcentage_' . $cle . $numEp] !== '') {
                        $mcccs[$session]['cc'][$numEp]->setPourcentage(Tools::convertToFloat($data['pourcentage_' . $cle . $numEp]));
                    } else {
                        $mcccs[$session]['cc'][$numEp]->setPourcentage(null);
                    }
                } else {
                    $mcccs[$session]['cc'][$numEp]->setPourcentage(50);
                }
                $mcccs[$session]['cc'][$numEp]->setNumeroEpreuve($numEp);
            } else {
                $mccc = new Mccc();
                $mccc->setEc($elementConstitutif);
                $mccc->setLibelle('Contrôle continu');
                $mccc->setControleContinu(true);
                $mccc->setExamenTerminal(false);
                $mccc->setNumeroSession($session);
                $mccc->setNumeroEpreuve($numEp);
                if ($typeD === 'L') {
                    $mccc->setNbEpreuves(2);
                } else {
                    $mccc->setNbEpreuves(1);
                }


                if (count($typeEpreuve_s1_cc) > 1) {
                    if (array_key_exists('pourcentage_' . $cle . $numEp, $data) && $data['pourcentage_' . $cle . $numEp] !== '') {
                        $mccc->setPourcentage(Tools::convertToFloat($data['pourcentage_' . $cle . $numEp]));
                    } else {
                        $mccc->setPourcentage(0);
                    }
                } else {
                    $mccc->setPourcentage(50);
                }
                $this->entityManager->persist($mccc);
                $mcccs[$session]['cc'][$numEp] = $mccc;
            }
            //todo: faire toutes les mises à jour en dehors du if, une fois l'objet repris ou créé. ideme autre methode de save

            if (isset($data['cc_has_tp_' . $cle . $numEp]) && $data['cc_has_tp_' . $cle . $numEp] === 'on') {
                $tab = [
                    'pourcentage' => $mcccs[$session]['cc'][$numEp]->getPourcentage(),
                    'cc_has_tp' => 'on'
                ];
            } else {
                $tab = [
                    'pourcentage' => 0,
                    'cc_has_tp' => false
                ];
            }
            $mcccs[$session]['cc'][$numEp]->setOptions($tab);
        }
        //suppression des MCCC
        foreach ($mcccs[$session]['cc'] as $mccc) {
            if (!in_array($mccc->getNumeroEpreuve(), $typeEpreuve_s1_cc)) {
                $this->entityManager->remove($mccc);
            }
        }

        return $mcccs;
    }

    private function verificationMccc(ElementConstitutif $ec, array $mcccs, $type = 'L')
    {
        switch ($ec->getTypeMccc()) {
            case 'cc':
                if (isset($mcccs[2]) && !isset($mcccs[2]['et']) && !is_array($mcccs[2]['et'])) {
                    return false;
                }

                if (!$this->verificationEt($mcccs[2]['et'])) {
                    return false;
                }


                if ($type === 'L') {
                    if (isset($mcccs[1]) && !isset($mcccs[1]['cc']) && count($mcccs[1]['cc']) !== 1) {
                        return false;
                    }

                    if ($mcccs[1]['cc'][1]->getPourcentage() === null || $mcccs[1]['cc'][1]->getPourcentage() !== 50.0) {
                        return false;
                    }

                    return true;
                }

                if (isset($mcccs[1]) && !isset($mcccs[1]['cc']) && !is_array($mcccs[1]['cc'])) {
                    return false;
                }

                $totPourcentage = 0.0;
                foreach ($mcccs[1]['cc'] as $mccc) {
                    if ($mccc->getPourcentage() === null || $mccc->getPourcentage() === 0.0) {
                        return false;
                    }
                    $totPourcentage += $mccc->getPourcentage();
                }
                return $totPourcentage === 100.0;
            case 'cci':
                if (isset($mcccs[1]) && !isset($mcccs[1]['cc'])) {
                    return false;
                }

                if (count($mcccs[1]['cc']) < 3) {
                    return false;
                }

                $totPourcentage = 0.0;
                foreach ($mcccs[1]['cc'] as $mccc) {
                    if ($mccc->getPourcentage() === null || $mccc->getPourcentage() === 0.0) {
                        return false;
                    }
                    $totPourcentage += $mccc->getPourcentage();
                }

                return $totPourcentage === 100.0;
            case 'cc_ct':
                if (isset($mcccs[1]) && !isset($mcccs[1]['cc']) && !is_array($mcccs[1]['cc'])) {
                    return false;
                }

                if ($mcccs[1]['cc'][1]->getPourcentage() === null || $mcccs[1]['cc'][1]->getPourcentage() === 0.0) {
                    return false;
                }

                if ($mcccs[1]['cc'][1]->getNbEpreuves() === null || $mcccs[1]['cc'][1]->getNbEpreuves() === 0) {
                    return false;
                }

                if (isset($mcccs[1]) && !isset($mcccs[1]['et']) && !is_array($mcccs[1]['et'])) {
                    return false;
                }

                $totPourcentage = $mcccs[1]['cc'][1]->getPourcentage();

                foreach ($mcccs[1]['et'] as $mccc) {
                    if ($mccc->getTypeEpreuve() === null || count($mccc->getTypeEpreuve()) === 0) {
                        return false;
                    }

                    if ($mccc->getPourcentage() === null || $mccc->getPourcentage() === 0.0) {
                        return false;
                    }

                    $totPourcentage += $mccc->getPourcentage();

                    if ($this->typeEpreuveHasDuree($mccc->getTypeEpreuve()[0]) && $mccc->getDuree() === null) {
                        return false;
                    }
                }

                if ($totPourcentage !== 100.0) {
                    return false;
                }

                if (isset($mcccs[2]) && !isset($mcccs[2]['et']) && !is_array($mcccs[2]['et'])) {
                    return false;
                }

                if (!$this->verificationEt($mcccs[2]['et'])) {
                    return false;
                }
                return true;
            case 'ct':
                if (isset($mcccs[1]) && !isset($mcccs[1]['et']) && !is_array($mcccs[1]['et'])) {
                    return false;
                }

                if (!$this->verificationEt($mcccs[1]['et'])) {
                    return false;
                }


                if (isset($mcccs[2]) && !isset($mcccs[2]['et']) && !is_array($mcccs[2]['et'])) {
                    return false;
                }

                if (!$this->verificationEt($mcccs[2]['et'])) {
                    return false;
                }
                return true;
        }
    }

    private function verificationEt(array $mcccs): bool
    {
        $totPourcentage = 0.0;

        foreach ($mcccs as $mccc) {
            if ($mccc->getTypeEpreuve() === null || count($mccc->getTypeEpreuve()) === 0) {
                return false;
            }

            if ($mccc->getPourcentage() === null || $mccc->getPourcentage() === 0.0) {
                return false;
            }

            $totPourcentage += $mccc->getPourcentage();

            if ($this->typeEpreuveHasDuree($mccc->getTypeEpreuve()[0]) && $mccc->getDuree() === null) {
                return false;
            }

            if ($totPourcentage === 100.0) {
                return true;
            }
        }

        return false;
    }

    private function typeEpreuveHasDuree(int|string $id): bool
    {
        return $this->typeEpreuves[$id]->isHasDuree() ?? false;
    }
}
