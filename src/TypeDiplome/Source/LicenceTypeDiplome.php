<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/Source/LicenceTypeDiplome.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\TypeDiplome\Source;

use App\Entity\ElementConstitutif;
use App\Entity\Formation;
use App\Entity\Mccc;
use App\Entity\Parcours;
use Symfony\Component\HttpFoundation\InputBag;

class LicenceTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'licence';
    public const TEMPLATE_FORM_MCCC = 'licence.html.twig';

    public string $libelle = 'Licence';

    public function saveMcccs(ElementConstitutif $elementConstitutif, InputBag $request): void
    {
        $mcccs = $this->getMcccs($elementConstitutif);
        if (count($mcccs) === 0) {
            $this->initMcccs($elementConstitutif);
            $this->entityManager->refresh($elementConstitutif);
            $mcccs = $this->getMcccs($elementConstitutif);
        }
        //todo: supprimer les lignes en trop selon le type...

        switch ($request->get('choix_type_mccc')) {
            case 'cc':
                //2 session, juste le type d'épreuve de la Session 2
                $mcccs[2]['et']->setTypeEpreuve([$request->get('typeEpreuve_s2_et')]);
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

                if (array_key_exists('et', $mcccs[1])) {
                    //supprimer
                    $this->entityManager->remove($mcccs[1]['et']);
                }

                break;
            case 'cci':

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
                break;
            case 'ct':
                //2 types d'épreuves à sauvegarder
                $mcccs[1]['et']->setTypeEpreuve([$request->get('typeEpreuve_s1_et')]);
                $mcccs[2]['et']->setTypeEpreuve([$request->get('typeEpreuve_s2_et')]);
                break;
        }


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

                $this->entityManager->flush();
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

        foreach ($mcccs as $mccc) {
            if ($mccc->isSecondeChance()) {
                $tabMcccs[3]['chance'] = $mccc;
            } elseif ($mccc->isControleContinu() === true && $mccc->isExamenTerminal() === false) {
                $tabMcccs[$mccc->getNumeroSession()]['cc'] = $mccc;
            } elseif ($mccc->isControleContinu() === false && $mccc->isExamenTerminal() === true) {
                $tabMcccs[$mccc->getNumeroSession()]['et'] = $mccc;
            }
        }

        return $tabMcccs;
    }
}
