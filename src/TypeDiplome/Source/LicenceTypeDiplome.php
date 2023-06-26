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
use App\TypeDiplome\Export\LicenceMccc;
use App\TypeDiplome\TypeDiplomeRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\InputBag;

class LicenceTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'licence';
    public const TEMPLATE_FORM_MCCC = 'licence.html.twig';
    public const NB_ANNEE = 3;

    public string $libelle = 'Licence';

    public function __construct(
        EntityManagerInterface $entityManager,
        TypeDiplomeRegistry    $typeDiplomeRegistry,
        protected LicenceMccc  $licenceMccc
    ) {
        parent::__construct($entityManager, $typeDiplomeRegistry);
    }

    public function exportExcelMccc(AnneeUniversitaire $anneeUniversitaire, Parcours $parcours)
    {
        return $this->licenceMccc->exportExcelLicenceMccc($anneeUniversitaire, $parcours);
    }

    public function saveMcccs(ElementConstitutif $elementConstitutif, InputBag $request): void
    {
        $mcccs = $this->getMcccs($elementConstitutif);
        if (count($mcccs) === 0) {
            $this->initMcccs($elementConstitutif);
            $this->entityManager->refresh($elementConstitutif);
            $mcccs = $this->getMcccs($elementConstitutif);
        }

        $isCompleted = null;

        switch ($request->get('choix_type_mccc')) {
            case 'cc':
                //2 session, juste le type d'épreuve de la Session 2

                if ($request->get('typeEpreuve_s2_et') === "") {
                    $mcccs[2]['et']->setTypeEpreuve(null);
                } else {
                    $mcccs[2]['et']->setTypeEpreuve([$request->get('typeEpreuve_s2_et')]);
                }


                if ($mcccs[2]['et']->getTypeEpreuve() !== null && count($mcccs[2]['et']->getTypeEpreuve()) > 0) {
                    $isCompleted = 'Complet';
                }

                if (array_key_exists('cc', $mcccs[1])) {
                    $mcccs[1]['cc']->setPourcentage(50);
                    $mcccs[1]['cc']->setNbEpreuves(2);
                } else {
                    $mccc = new Mccc();
                    $mccc->setEc($elementConstitutif);
                    $mccc->setPourcentage(50);
                    $mccc->setNbEpreuves(2);
                    $mccc->setNumeroSession(1);
                    $mccc->setControleContinu(true);
                    $mccc->setExamenTerminal(false);
                    $mcccs[1]['cc'] = $mccc;
                    $this->entityManager->persist($mccc);
                }
                break;
            case 'cci':
                $pourcentages = $request->all()['pourcentage'];
                //supprimer les MCCC en trop
                foreach ($mcccs as $mccc) {
                    if (!array_key_exists($mccc->getNumeroSession(), $pourcentages)) {
                        $this->entityManager->remove($mccc);
                    }
                }

                $totalPourcentage = 0;
                $nbNotes = 0;

                $this->entityManager->flush();
                foreach ($pourcentages as $key => $pourcentage) {
                    if (array_key_exists($key, $mcccs)) {
                        $mcccs[$key]->setPourcentage((float)$pourcentage);
                    } else {
                        $mccc = new Mccc();
                        $mccc->setEc($elementConstitutif);
                        $mccc->setLibelle('Contrôle continu intégral ' . $key);
                        $mccc->setPourcentage((float)$pourcentage);
                        $mccc->setNbEpreuves(1);
                        $mccc->setNumeroSession((int)$key);
                        $mccc->setControleContinu(true);
                        $mccc->setExamenTerminal(false);
                        $mcccs[$key] = $mccc;
                        $this->entityManager->persist($mccc);
                    }
                    $totalPourcentage += (float)$pourcentage;
                    $nbNotes++;
                }

                if ($totalPourcentage === 100.0 && $nbNotes >= 3) {
                    $isCompleted = 'Complet';
                }
                break;
            case 'cc_ct':
                //cas classique
                $mcccs[1]['cc']->setPourcentage((float)$request->get('pourcentage_s1_cc'));
                $mcccs[1]['cc']->setNbEpreuves((int)$request->get('nbepreuve_s1_cc'));

                $mcccs[1]['et']->setPourcentage((float)$request->get('pourcentage_s1_et'));
                if ((float)$request->get('pourcentage_s1_et') > 0) {
                    $mcccs[1]['et']->setNbEpreuves(1);
                    $mcccs[1]['et']->setTypeEpreuve([$request->get('typeEpreuve_s1_et')]);
                } else {
                    $mcccs[1]['et']->setNbEpreuves(0);
                    $mcccs[1]['et']->setTypeEpreuve([]);
                }

                $mcccs[2]['et']->setPourcentage(100);
                $mcccs[2]['et']->setNbEpreuves(1);
                $mcccs[2]['et']->setTypeEpreuve([$request->get('typeEpreuve_s2_et')]);

                if (
                    $mcccs[1]['cc']->getNbEpreuves() > 0 &&
                    $mcccs[1]['et']->getTypeEpreuve() !== null &&
                    $mcccs[2]['et']->getTypeEpreuve() !== null &&
                    $mcccs[1]['cc']->getPourcentage() + $mcccs[1]['et']->getPourcentage() === 100.00
                ) {
                    $isCompleted = 'Complet';
                }

                break;
            case 'ct':
                //2 types d'épreuves à sauvegarder
                if ($request->get('typeEpreuve_s1_et') === "") {
                    $mcccs[1]['et']->setTypeEpreuve(null);
                } else {
                    $mcccs[1]['et']->setTypeEpreuve([$request->get('typeEpreuve_s1_et')]);
                }

                if ($request->get('typeEpreuve_s2_et') === "") {
                    $mcccs[2]['et']->setTypeEpreuve(null);
                } else {
                    $mcccs[2]['et']->setTypeEpreuve([$request->get('typeEpreuve_s2_et')]);
                }

                if ($mcccs[1]['et']->getTypeEpreuve() !== null && count($mcccs[1]['et']->getTypeEpreuve()) > 0 &&
                    $mcccs[2]['et']->getTypeEpreuve() !== null && count($mcccs[2]['et']->getTypeEpreuve()) > 0) {
                    $isCompleted = 'Complet';
                }

                break;
        }

        $elementConstitutif->setEtatMccc($isCompleted);

        $this->entityManager->flush();
    }

    public function saveMccc(ElementConstitutif $elementConstitutif, string $field, mixed $value): void
    {
        $mcccs = $this->getMcccs($elementConstitutif);

        switch ($field) {
            case 'pourcentage_s1_cc':
                $mcccs[1]['cc']->setPourcentage((float)$value);
                break;
            case 'nbepreuve_s1_cc':
                $mcccs[1]['cc']->setNbEpreuves((int)$value);
                break;
            case 'pourcentage_s1_et':
                $mcccs[1]['et']->setPourcentage((float)$value);
                if ((float)$value <= 0) {
                    $mcccs[1]['et']->setNbEpreuves(0);
                    $mcccs[1]['et']->setTypeEpreuve([]);
                }
                break;
            case 'typeEpreuve_s1_et':
                $mcccs[1]['et']->setTypeEpreuve([$value]);
                if (count($mcccs[1]['et']->getTypeEpreuve()) > 0) {
                    $mcccs[1]['et']->setNbEpreuves(1);
                }
                break;
            case 'typeEpreuve_s2_et':
                $mcccs[2]['et']->setTypeEpreuve([$value]);
                $mcccs[2]['et']->setNbEpreuves(1);
                $mcccs[2]['et']->setPourcentage(100);
                break;
        }

        $this->entityManager->flush();
    }


    public function initMcccs(ElementConstitutif $elementConstitutif): void
    {
        switch ($elementConstitutif->getTypeMccc()) {
            case 'cc':
                $mccc = new Mccc();
                $mccc->setEc($elementConstitutif);
                $mccc->setLibelle('Contrôle continu');
                $mccc->setControleContinu(true);
                $mccc->setExamenTerminal(false);
                $mccc->setNumeroSession(1);
                $mccc->setNbEpreuves(2);
                $mccc->setPourcentage(50);
                $this->entityManager->persist($mccc);

                $mccc = new Mccc();
                $mccc->setEc($elementConstitutif);
                $mccc->setLibelle('Examen 2e session');
                $mccc->setControleContinu(false);
                $mccc->setExamenTerminal(true);
                $mccc->setNumeroSession(2);
                $mccc->setNbEpreuves(1);
                $mccc->setPourcentage(100);
                $this->entityManager->persist($mccc);
                break;
            case 'cci':
                $mccc = new Mccc();
                $mccc->setEc($elementConstitutif);
                $mccc->setLibelle('Contrôle continu intégral 1');
                $mccc->setControleContinu(true);
                $mccc->setExamenTerminal(false);
                $mccc->setNumeroSession(1);
                $mccc->setNbEpreuves(1);
                $mccc->setPourcentage(0);
                $this->entityManager->persist($mccc);

                $mccc = new Mccc();
                $mccc->setEc($elementConstitutif);
                $mccc->setLibelle('Contrôle continu intégral 2');
                $mccc->setControleContinu(true);
                $mccc->setExamenTerminal(false);
                $mccc->setNumeroSession(2);
                $mccc->setNbEpreuves(1);
                $mccc->setPourcentage(0);
                $this->entityManager->persist($mccc);

                $mccc = new Mccc();
                $mccc->setEc($elementConstitutif);
                $mccc->setLibelle('Contrôle continu intégral 3');
                $mccc->setControleContinu(true);
                $mccc->setExamenTerminal(false);
                $mccc->setNumeroSession(3);
                $mccc->setNbEpreuves(1);
                $mccc->setPourcentage(0);
                $this->entityManager->persist($mccc);

                break;
            case 'cc_ct':
                //cas classique
                //1ere session
                // Contrôle continu
                $mccc = new Mccc();
                $mccc->setEc($elementConstitutif);
                $mccc->setLibelle('Contrôle continu');
                $mccc->setControleContinu(true);
                $mccc->setExamenTerminal(false);
                $mccc->setNumeroSession(1);
                $this->entityManager->persist($mccc);

                // Examen terminal
                $mccc = new Mccc();
                $mccc->setEc($elementConstitutif);
                $mccc->setLibelle('Examen terminal');
                $mccc->setControleContinu(false);
                $mccc->setExamenTerminal(true);
                $mccc->setNumeroSession(1);
                $this->entityManager->persist($mccc);

                //2eme session
                $mccc = new Mccc();
                $mccc->setEc($elementConstitutif);
                $mccc->setLibelle('2éme session');
                $mccc->setNumeroSession(2);
                $mccc->setControleContinu(false);
                $mccc->setExamenTerminal(true);
                $this->entityManager->persist($mccc);
                break;
            case 'ct':
                //2 types d'épreuves à sauvegarder
                $mccc = new Mccc();
                $mccc->setEc($elementConstitutif);
                $mccc->setLibelle('Contrôle terminal');
                $mccc->setControleContinu(false);
                $mccc->setExamenTerminal(true);
                $mccc->setNumeroSession(1);
                $mccc->setNbEpreuves(1);
                $mccc->setPourcentage(100);
                $this->entityManager->persist($mccc);

                $mccc = new Mccc();
                $mccc->setEc($elementConstitutif);
                $mccc->setLibelle('Examen 2e session');
                $mccc->setControleContinu(false);
                $mccc->setExamenTerminal(true);
                $mccc->setNumeroSession(2);
                $mccc->setNbEpreuves(1);
                $mccc->setPourcentage(100);
                $this->entityManager->persist($mccc);
                break;
        }

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
                    $tabMcccs[$mccc->getNumeroSession()]['cc'] = $mccc;
                } elseif ($mccc->isControleContinu() === false && $mccc->isExamenTerminal() === true) {
                    $tabMcccs[$mccc->getNumeroSession()]['et'] = $mccc;
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
}
