<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/Bcc.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:09
 */

namespace App\Classes;

use App\Entity\BlocCompetence;
use App\Entity\Competence;
use App\Entity\Parcours;
use App\Repository\BlocCompetenceRepository;
use App\Repository\CompetenceRepository;
use App\Repository\ParcoursRepository;
use Doctrine\ORM\EntityManagerInterface;

class Bcc
{
    public function __construct(
        private readonly BlocCompetenceRepository $blocCompetenceRepository,
        private readonly CompetenceRepository $competenceRepository,
        private readonly ParcoursRepository $parcoursRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function recopieBcc(Parcours $parcoursDestination, mixed $value): bool
    {
        $parcoursOrigine = $this->parcoursRepository->find($value);

        if ($parcoursOrigine) {
            $bccs = $parcoursOrigine->getBlocCompetences();

            foreach ($bccs as $bcc) {
                $bc = clone $bcc;
                $bc->setParcours($parcoursDestination);
                $this->entityManager->persist($bc);

                foreach ($bcc->getCompetences() as $competence) {
                    $c = clone $competence;
                    $c->setBlocCompetence($bc);
                    $this->entityManager->persist($c);
                }
            }
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    public function deplacerBlocCompetence(BlocCompetence $blocCompetence, string $sens): bool
    {
        //modifie l'ordre de la ressource
        $ordreInitial = $blocCompetence->getOrdre();

        if ($sens === 'up') {
            $ordreDestination = $ordreInitial - 1;
        } else {
            $ordreDestination = $ordreInitial + 1;
        }

        //récupère toutes les ressources à déplacer
        return $this->inverseBcc($ordreInitial, $ordreDestination, $blocCompetence);
    }

    public function deplacerCompetence(Competence $competence, string $sens): bool
    {
        $ordreInitial = $competence->getOrdre();

        if ($sens === 'up') {
            $ordreDestination = $ordreInitial - 1;
        } else {
            $ordreDestination = $ordreInitial + 1;
        }

        //récupère toutes les ressources à déplacer
        return $this->inverseCc($ordreInitial, $ordreDestination, $competence);
    }

    private function inverseBcc(?int $ordreInitial, ?int $ordreDestination, BlocCompetence $blocCompetence): bool
    {
        $sae = $this->blocCompetenceRepository->findOneBy([
            'ordre' => $ordreDestination,
            'parcours' => $blocCompetence->getParcours()->getId()
        ]);
        $blocCompetence->setOrdre($ordreDestination);
        $blocCompetence->genereCode();


        if ($sae !== null) {
            $sae->setOrdre($ordreInitial);
            $sae->genereCode();
        }

        foreach ($blocCompetence->getCompetences() as $comp) {
            $comp->genereCode();
        }

        foreach ($sae->getCompetences() as $comp) {
            $comp->genereCode();
        }

        $this->entityManager->flush();

        return true;
    }

    private function inverseCc(?int $ordreInitial, ?int $ordreDestination, Competence $competence): bool
    {
        $sae = $this->competenceRepository->findOneBy([
            'ordre' => $ordreDestination,
            'blocCompetence' => $competence->getBlocCompetence()->getId()
        ]);
        $competence->setOrdre($ordreDestination);
        $competence->genereCode();

        if ($sae !== null) {
            $sae->setOrdre($ordreInitial);
            $sae->genereCode();
        }

        $this->entityManager->flush();

        return true;
    }

    public function resetBcc(Parcours $parcours): void
    {
        //on supprime les BCC et les compétences du parcours et des fiches matières
        foreach ($parcours->getBlocCompetences() as $bcc) {
            foreach ($bcc->getCompetences() as $comp) {
                foreach ($comp->getFicheMatieress() as $fm) {
                    $fm->removeCompetence($comp);
                }
                $this->entityManager->remove($comp);
            }
            $this->entityManager->remove($bcc);
        }
        $this->entityManager->flush();
    }

    public function renumeroteCompetences(?BlocCompetence $blocCompetence): void
    {
        if ($blocCompetence === null) {
            return;
        }

        $i = 1;
        foreach ($blocCompetence->getCompetences() as $competence) {
            $competence->setOrdre($i);
            $competence->genereCode();
            ++$i;
        }
        $this->entityManager->flush();
    }

    public function renumeroteBlocCompetence(?Parcours $parc)
    {
        if ($parc === null) {
            return;
        }

        $i = 1;
        foreach ($parc->getBlocCompetences() as $bcc) {
            $bcc->setOrdre($i);
            $bcc->genereCode();
            foreach ($bcc->getCompetences() as $competence) {
                $competence->genereCode();
            }
            ++$i;
        }
        $this->entityManager->flush();
    }
}
