<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/FormationStructure.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:10
 */

namespace App\Classes;

use App\Entity\CampagneCollecte;
use App\Entity\DpeParcours;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\SemestreParcours;
use App\Entity\Ue;
use App\Repository\SemestreParcoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Workflow\WorkflowInterface;

class FormationStructure
{
    public function __construct(
        protected SemestreParcoursRepository $semestreParcoursRepository,
        protected EntityManagerInterface     $entityManager,
        protected WorkflowInterface          $dpeParcoursWorkflow,
    ) {
    }

    public function genereStructure(Parcours $parcours, CampagneCollecte $campagneCollecte): void
    {
        $formation = $parcours->getFormation();
        if ($formation === null) {
            throw new RuntimeException('La formation n\'est pas définie');
        }

        $this->genereStructureFormation($formation, $campagneCollecte, $parcours);
    }

    public function genereStructrePasParcours(Formation $formation, CampagneCollecte $campagneCollecte): void
    {
        $this->genereStructureFormation($formation, $campagneCollecte, $formation->getParcours()->first());
    }

    private function genereStructureFormation(Formation $formation, CampagneCollecte $campagneCollecte, bool|Parcours|null $parcours = null): void
    {
        if ($formation->getTypeDiplome() === null) {
            throw new RuntimeException('Le type de diplôme n\'est pas défini');
        }

        if ($parcours instanceof Parcours) {
            $this->deleteStructure($parcours);
        }

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
                $parcours->setRespParcours($formation->getResponsableMention());
                $parcours->setModalitesEnseignement(null);
                $semestres = [];

                $formation->addParcour($parcours);
                $parcours->setFormation($formation);

                $dpeParcours = new DpeParcours();
                $dpeParcours->setParcours($parcours);
                $dpeParcours->setFormation($formation);
                $dpeParcours->setCampagneCollecte($campagneCollecte);
                $this->entityManager->persist($dpeParcours);
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
        if ($parcours->getFormation() !== null && $parcours->getFormation()->isHasParcours() === true) {
            foreach ($parcours->getFormation()->getParcours() as $parcoursFormation) {
                if ($parcoursFormation->getId() !== $parcours->getId()) {
                    foreach ($parcoursFormation->getSemestreParcours() as $semestreParcours) {
                        $semestre = $semestreParcours->getSemestre();
                        if ($semestre !== null && $semestre->isTroncCommun()) {
                            $tSemestres[$semestre->getOrdre()] = $semestre;
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

    public function recopieParcours(Parcours $parcours, ?Parcours $parcoursOriginal): void
    {
        if ($parcoursOriginal !== null) {
            $this->deleteStructure($parcours);
        }

        foreach ($parcoursOriginal->getSemestreParcours() as $semestreParcour) {
            if ($semestreParcour->getSemestre()?->isTroncCommun()) {
                $spNew = new SemestreParcours($semestreParcour->getSemestre(), $parcours);
                $this->entityManager->persist($spNew);
            } else {
                //Pas tronc commun, on duplique semestre, UE et EC
                $newSemestre = clone $semestreParcour->getSemestre();
                $this->entityManager->persist($newSemestre);
                $newSp = new SemestreParcours($newSemestre, $parcours);
                $this->entityManager->persist($newSp);

                foreach ($semestreParcour->getSemestre()?->getUes() as $ue) {
                    if ($ue->getUeParent() === null) {
                        $newUe = clone $ue;
                        $newUe->setSemestre($newSemestre);
                        $this->entityManager->persist($newUe);

                        //dupliquer les EC des ue
                        foreach ($ue->getElementConstitutifs() as $ec) {
                            $newEc = clone $ec;
                            $newEc->setUe($newUe);
                            $newEc->setParcours($parcours);
                            $this->entityManager->persist($newEc);
                        }
                    }
                }
            }

            $this->entityManager->flush();
        }
    }

    public function updateStructureDepart(Formation $formation, ?int $semestreInitialDebut, int $semestreNouveauDebut): void
    {
        $semestresToDelete = [];
        $semParc = $this->semestreParcoursRepository->findByParcoursOrdreInferieur($formation, $semestreNouveauDebut);
        $semestres = $formation->getStructureSemestres();

        // on regarde si on doit supprimer ou ajouter des semestres
        if ($semestreInitialDebut === null || $semestreInitialDebut > $semestreNouveauDebut) {
            //on ajoute
            for ($i = $semestreNouveauDebut; $i < $semestreInitialDebut; $i++) {
                $semestres[$i] = '';
            }

            ksort($semestres);
        } else {
            //on supprime
            for ($i = 0; $i < $semestreNouveauDebut; $i++) {
                if (array_key_exists($i, $semestres)) {
                    unset($semestres[$i]);
                }

                foreach ($semParc as $sp) {
                    $semestresToDelete[] = $sp->getSemestre();
                    $this->entityManager->remove($sp);
                }

                $semestresToDelete = array_unique($semestresToDelete);
                //supprimer réellement le semestre et les UE ??
                foreach ($semestresToDelete as $semestre) {
                    $this->entityManager->remove($semestre);
                }
            }
        }
        $formation->setStructureSemestres($semestres);
        $this->entityManager->flush();
    }
}
