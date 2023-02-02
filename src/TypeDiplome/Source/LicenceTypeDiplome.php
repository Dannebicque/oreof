<?php

namespace App\TypeDiplome\Source;

use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\SemestreParcours;
use App\Entity\Ue;
use PHPStan\PhpDoc\Tag\ParamOutTag;

class LicenceTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'licence';
    public const TEMPLATE = 'licence.html.twig';

    public string $libelle = 'Licence';
    public int $nbSemestres = 6;

    public function initParcours(Parcours $parcours): void
    {
        // TODO: Implement initParcours() method.
    }

    public function genereStructure(Formation $formation): void
    {
        //semestres
        $semestres = $formation->getStructureSemestres();

        if ($formation->isHasParcours() === false && $formation->getParcours()->count() === 0) {
            $parcours = new Parcours($formation); //parcours par défaut
            $parcours->setLibelle('Parcours par défaut');
            $semestres = [
                1 => 'tronc_commun',
                2 => 'tronc_commun',
                3 => 'tronc_commun',
                4 => 'tronc_commun',
                5 => 'tronc_commun',
                6 => 'tronc_commun',
            ];

            $this->entityManager->persist($parcours);
        }

        $tSemestres = [];
        foreach ($formation->getParcours() as $parcours) {
            foreach ($semestres as $key => $format) {
                $semestre = null;
                if ($format === 'tronc_commun') {
                    if (array_key_exists($key, $tSemestres) === false) {
                        $semestre = new Semestre();
                        $semestre->setOrdre($key);
                        $semestre->setTroncCommun(true);

                        $this->entityManager->persist($semestre);
                        $tSemestres[$key] = $semestre;
                        $this->generesUe($semestre);
                    } else {
                        $semestre = $tSemestres[$key];
                    }

                    $sp = new SemestreParcours($semestre, $parcours);
                    $this->entityManager->persist($sp);
                    $parcours->addSemestreParcour($sp);
                    $semestre->addSemestreParcour($sp);

                } else {

                    $semestre = new Semestre();
                    $semestre->setOrdre($key);
                    $this->entityManager->persist($semestre);
                    $this->generesUe($semestre);
                    $sp = new SemestreParcours($semestre, $parcours);
                    $this->entityManager->persist($sp);
                    $parcours->addSemestreParcour($sp);
                    $semestre->addSemestreParcour($sp);
                }
            }
        }

        $this->entityManager->flush();
    }

    private function generesUe(Semestre $semestre): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $ue = new Ue();
            $ue->setSemestre($semestre);
            $ue->setOrdre($i);
            $this->entityManager->persist($ue);
        }
    }
}

