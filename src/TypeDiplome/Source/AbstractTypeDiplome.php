<?php

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

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected TypeDiplomeRegistry $typeDiplomeRegistry
    )
    {
    }

    public function deleteStructure(Formation $formation): void
    {
        foreach ($formation->getParcours() as $parcours) {
            foreach ($parcours->getSemestreParcours() as $semestre)
            {
                foreach ($semestre->getSemestre()?->getUes() as $ue)
                {
                    foreach ($ue->getEcUes() as $ecUe)
                    {
                        $this->entityManager->remove($ecUe->getUe());
                        $this->entityManager->remove($ecUe);
                    }

                    $this->entityManager->remove($ue);

                }
                $this->entityManager->remove($semestre->getSemestre());
                $this->entityManager->remove($semestre);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    public function genereStructure(Formation $formation): void
    {
        $this->typeDiplome = $this->typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome());
        $this->deleteStructure($formation);
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

            for ($i = $formation->getSemestreDebut(); $i <= $this->typeDiplome->nbSemestres; $i++) {
                $semestres[$i] = 'tronc_commun';
            }
        }

        $this->abstractGenereStructure($formation, $semestres);

        $this->entityManager->flush();
    }


//définition générale
    public function abstractGenereStructure(Formation $formation, array $semestres): void
    {
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
    }

    private function generesUe(Semestre $semestre): void
    {
        for ($i = 1; $i <= $this->typeDiplome->nbUes; $i++) {
            $ue = new Ue();
            $ue->setSemestre($semestre);
            $ue->setOrdre($i);
            $this->entityManager->persist($ue);
        }
    }

}
