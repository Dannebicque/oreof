<?php

namespace App\TypeDiplome\Source;

use App\Entity\ElementConstitutif;
use App\Entity\Formation;
use App\Entity\Mccc;
use App\Entity\Parcours;
use Symfony\Component\HttpFoundation\InputBag;

class LicenceTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'licence';
    public const TEMPLATE = 'licence.html.twig';
    public const TEMPLATE_FORM_MCCC = 'licence.html.twig';

    public string $libelle = 'Licence';
    public int $nbSemestres = 6;
    public int $nbUes = 5;

    public function saveMcccs(ElementConstitutif $elementConstitutif, InputBag $request): void
    {
        $mcccs = $this->getMcccs($elementConstitutif);

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

        $mcccs[1]['chance']->setPourcentage((float)$request->get('pourcentage_s1_chance'));
        if ((float)$request->get('pourcentage_s1_chance') > 0) {
            $mcccs[1]['chance']->setNbEpreuves(count($request->all()['typeEpreuve_s1_chance']));
            $mcccs[1]['chance']->setTypeEpreuve($request->all()['typeEpreuve_s1_chance']);
        } else {
            $mcccs[1]['chance']->setNbEpreuves(0);
            $mcccs[1]['chance']->setTypeEpreuve([]);
        }

        $mcccs[2]['et']->setPourcentage(100);
        $mcccs[2]['et']->setNbEpreuves(1);
        $mcccs[2]['et']->setTypeEpreuve([$request->get('typeEpreuve_s2_et')]);

        $this->entityManager->flush();

    }

    public function initMcccs(ElementConstitutif $elementConstitutif): void
    {
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

        //2eme Chance, première session
        $mccc = new Mccc();
        $mccc->setEc($elementConstitutif);
        $mccc->setLibelle('2éme chance');
        $mccc->setNumeroSession(1);
        $mccc->setSecondeChance(true);
        $mccc->setControleContinu(true);
        $mccc->setExamenTerminal(false);
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
    }

    public function getMcccs(ElementConstitutif $elementConstitutif): array
    {
        $mcccs = $elementConstitutif->getMcccs();
        $tabMcccs = [];

        foreach ($mcccs as $mccc) {
            if ($mccc->isSecondeChance()) {
                $tabMcccs[$mccc->getNumeroSession()]['chance'] = $mccc;
            } elseif ($mccc->isControleContinu() === true && $mccc->isExamenTerminal() === false) {
                $tabMcccs[$mccc->getNumeroSession()]['cc'] = $mccc;
            } elseif ($mccc->isControleContinu() === false && $mccc->isExamenTerminal() === true) {
                $tabMcccs[$mccc->getNumeroSession()]['et'] = $mccc;
            }
        }

        return $tabMcccs;
    }
}

