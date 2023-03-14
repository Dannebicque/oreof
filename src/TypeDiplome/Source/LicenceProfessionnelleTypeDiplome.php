<?php

namespace App\TypeDiplome\Source;

use App\Entity\ElementConstitutif;
use App\Entity\Formation;
use App\Entity\Mccc;
use App\Entity\Parcours;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\InputBag;

class LicenceProfessionnelleTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'licence_professionnelle';
    public const TEMPLATE = 'lp.html.twig';
    public const TEMPLATE_FORM_MCCC = 'lp.html.twig';

    public string $libelle = 'Licence Professionnelle';

    public int $nbSemestres = 6;
    public int $nbUes = 0;

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

        $mcccs[3]['chance']->setPourcentage((float)$request->get('pourcentage_s1_chance'));
        if ((float)$request->get('pourcentage_s1_chance') > 0) {
            $mcccs[3]['chance']->setNbEpreuves(count($request->all()['typeEpreuve_s1_chance']));
            $mcccs[3]['chance']->setTypeEpreuve($request->all()['typeEpreuve_s1_chance']);
        } else {
            $mcccs[3]['chance']->setNbEpreuves(0);
            $mcccs[3]['chance']->setTypeEpreuve([]);
        }

        $mcccs[2]['et']->setPourcentage(100);
        $mcccs[2]['et']->setNbEpreuves(1);
        $mcccs[2]['et']->setTypeEpreuve([$request->get('typeEpreuve_s2_et')]);

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
                if (count( $mcccs[1]['et']->getTypeEpreuve())> 0) {
                    $mcccs[1]['et']->setNbEpreuves(1);
                }
                break;

            case 'pourcentage_s1_chance':
                $mcccs[3]['chance']->setPourcentage((float)$value);
                if ((float)$value <= 0) {
                    $mcccs[3]['chance']->setNbEpreuves(0);
                    $mcccs[3]['chance']->setTypeEpreuve([]);
                }
                break;
            case 'typeEpreuve_s1_chance':
                $mcccs[3]['chance']->setTypeEpreuve($value);//est un tableau
                if (count( $mcccs[3]['chance']->getTypeEpreuve())> 0) {
                    $mcccs[3]['chance']->setNbEpreuves(count($value));
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
        $mccc->setNumeroSession(3);
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
                $tabMcccs[3]['chance'] = $mccc;
            } elseif ($mccc->isControleContinu() === true && $mccc->isExamenTerminal() === false) {
                $tabMcccs[$mccc->getNumeroSession()]['cc'] = $mccc;
            } elseif ($mccc->isControleContinu() === false && $mccc->isExamenTerminal() === true) {
                $tabMcccs[$mccc->getNumeroSession()]['et'] = $mccc;
            }
        }

        return $tabMcccs;
    }


    public function genereStructure(Parcours $parcours, Formation $formation): void
    {
        $this->deleteStructure($parcours);
        //semestres
        $semestres = $formation->getStructureSemestres();

        if ($formation->isHasParcours() === false) {
            if ($formation->getParcours()->count() === 0) {
                $parcours = new Parcours($formation); //parcours par défaut
                $parcours->setLibelle('Parcours par défaut');
                $semestres = [];

                $formation->addParcour($parcours);
                $parcours->setFormation($formation);
                $this->entityManager->persist($parcours);
            }

            for ($i = $formation->getSemestreDebut(); $i <= $this->nbSemestres; $i++) {
                $semestres[$i] = 'tronc_commun';
            }
        }

        $this->abstractGenereStructure($parcours, $semestres);

        $this->entityManager->flush();
    }
}
