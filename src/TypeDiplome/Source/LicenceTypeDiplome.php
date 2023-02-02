<?php

namespace App\TypeDiplome\Source;

use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\Ue;

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
        foreach ($semestres as $key => $format) {
            $semestre = null;
            if ($format === 'tronc_commun') {
                $semestre = new Semestre();
                $semestre->setFormation($formation);
                $semestre->setOrdre($key);
                $this->entityManager->persist($semestre);
            } else {
                foreach ($formation->getParcours() as $parcours) {
                    $semestre = new Semestre();
                    $semestre->setParcours($parcours);
                    $semestre->setOrdre($key);
                    $this->entityManager->persist($semestre);
                }
            }
            if ($semestre !== null) {
                $this->generesUe($semestre);
            }
        }

        $this->entityManager->flush();
    }

    private function generesUe(Semestre $semestre): void
    {
        for($i = 1; $i <= 5; $i++) {
            $ue = new Ue();
            $ue->setSemestre($semestre);
            $ue->setOrdre($i);
            $this->entityManager->persist($ue);
        }
    }
}

