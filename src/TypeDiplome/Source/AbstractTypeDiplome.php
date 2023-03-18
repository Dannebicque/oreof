<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/Source/AbstractTypeDiplome.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\TypeDiplome\Source;

use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\SemestreParcours;
use App\Entity\Ue;
use App\TypeDiplome\TypeDiplomeRegistry;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractTypeDiplome
{
    private ?TypeDiplomeInterface $typeDiplome;
    public int $nbSemestres = 0;
    public int $nbUes = 0;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected TypeDiplomeRegistry $typeDiplomeRegistry
    ) {
    }

    public function deleteStructure(Parcours $parcours): void
    {
        foreach ($parcours->getSemestreParcours() as $semestre) {
            foreach ($semestre->getSemestre()?->getUes() as $ue) {
                foreach ($ue->getEcUes() as $ecUe) {
                    $this->entityManager->remove($ecUe->getUe());
                    $this->entityManager->remove($ecUe);
                }

                $this->entityManager->remove($ue);
            }
            $this->entityManager->remove($semestre->getSemestre());
            $this->entityManager->remove($semestre);
        }

        $this->entityManager->flush();
    }

//définition générale
    public function abstractGenereStructure(Parcours $parcours, array $semestres): void
    {
        $tSemestres = [];

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
            } else {
                $semestre = new Semestre();
                $semestre->setOrdre($key);
                $this->entityManager->persist($semestre);
                $this->generesUe($semestre);
            }
            $sp = new SemestreParcours($semestre, $parcours);
            $this->entityManager->persist($sp);
            $parcours->addSemestreParcour($sp);
            $semestre->addSemestreParcour($sp);
        }
    }

    private function generesUe(Semestre $semestre): void
    {
        for ($i = 1; $i <= $this->nbUes; $i++) {
            $ue = new Ue();
            $ue->setSemestre($semestre);
            $ue->setOrdre($i);
            $this->entityManager->persist($ue);
        }
    }
}
