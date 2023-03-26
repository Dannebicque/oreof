<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/FormationStructure.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:10
 */

namespace App\Classes;

use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\SemestreParcours;
use App\Entity\Ue;
use App\TypeDiplome\TypeDiplomeRegistry;
use Doctrine\ORM\EntityManagerInterface;

class FormationStructure
{
    public function __construct(
        protected EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    public function genereStructre(Parcours $parcours): void
    {
        $formation = $parcours->getFormation();
        if ($formation === null) {
            throw new \RuntimeException('La formation n\'est pas définie');
        }

        $this->genereStructure($formation, $parcours);
    }

    public function genereStructrePasParcours(Formation $formation): void
    {
        $this->genereStructure($formation, $formation->getParcours()->first());
    }

    private function genereStructure(Formation $formation, bool|Parcours|null $parcours = null): void
    {
        if ($formation->getTypeDiplome() === null) {
            throw new \RuntimeException('Le type de diplôme n\'est pas défini');
        }

        if ($parcours !== null && $parcours instanceof Parcours) {
            $this->deleteStructure($parcours);
        }

        //todo: tester si debut semestre flexible
        if ($formation->getTypeDiplome()->isDebutSemestreFlexible() === false) {
            $semestreDebut = $formation->getTypeDiplome()->getSemestreDebut();
        } else {
            $semestreDebut = $formation->getSemestreDebut();
        }
        $nbUes = $formation->getTypeDiplome()->getNbUeMax();

        //semestres
        $semestres = $formation->getStructureSemestres();

        if ($formation->isHasParcours() === false) {
            if ($formation->getParcours()->count() === 0) {
                $parcours = new Parcours($formation); //parcours par défaut
                $parcours->setLibelle(Parcours::PARCOURS_DEFAUT);
                $parcours->setModalitesEnseignement(null);
                $semestres = [];

                $formation->addParcour($parcours);
                $parcours->setFormation($formation);
                $this->entityManager->persist($parcours);
            }

            for ($i = $semestreDebut; $i <= $formation->getTypeDiplome()->getSemestreFin(); $i++) {
                $semestres[$i] = 'tronc_commun';
            }
        }

        $this->abstractGenereStructure($parcours, $semestres, $nbUes);

        $this->entityManager->flush();
    }

    private function deleteStructure(Parcours $parcours): void
    {
        foreach ($parcours->getSemestreParcours() as $semestre) {
            foreach ($semestre->getSemestre()?->getUes() as $ue) {
                foreach ($ue->getElementConstitutifs() as $ec) {
                    $this->entityManager->remove($ec);
                }

                $this->entityManager->remove($ue);
            }
            $this->entityManager->remove($semestre->getSemestre());
            $this->entityManager->remove($semestre);
        }

        $this->entityManager->flush();
    }

    private function abstractGenereStructure(Parcours $parcours, array $semestres, int $nbUes): void
    {
        $tSemestres = [];

        //récupérer les semestres de tronc commun existants pour les autres parcours de la formation
        if ($parcours->getFormation()->isHasParcours() === true) {
            foreach ($parcours->getFormation()->getParcours() as $parcoursFormation) {
                if ($parcoursFormation->getId() !== $parcours->getId()) {
                    foreach ($parcoursFormation->getSemestreParcours() as $semestreParcours) {
                        $semestre = $semestreParcours->getSemestre();
                        if ($semestre !== null && $semestre->isTroncCommun()) {
                            $tSemestres[$semestre?->getOrdre()] = $semestre;
                        }
                    }
                }
            }
        }

        foreach ($semestres as $key => $format) {
            $semestre = null;
            if ($format === 'tronc_commun') {
                if (array_key_exists($key, $tSemestres) === false) {
                    $semestre = new Semestre();
                    $semestre->setOrdre($key);
                    $semestre->setTroncCommun(true);

                    $this->entityManager->persist($semestre);
                    $tSemestres[$key] = $semestre;
                    $this->generesUe($semestre, $nbUes);
                } else {
                    $semestre = $tSemestres[$key];
                }
            } else {
                $semestre = new Semestre();
                $semestre->setOrdre($key);
                $this->entityManager->persist($semestre);
                $this->generesUe($semestre, $nbUes);
            }
            $sp = new SemestreParcours($semestre, $parcours);
            $this->entityManager->persist($sp);
            $parcours->addSemestreParcour($sp);
            $semestre->addSemestreParcour($sp);
        }
    }

    private function generesUe(Semestre $semestre, int $nbUes): void
    {
        for ($i = 1; $i <= $nbUes; $i++) {
            $ue = new Ue();
            $ue->setSemestre($semestre);
            $ue->setOrdre($i);
            $this->entityManager->persist($ue);
        }
    }
}
