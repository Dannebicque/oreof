<?php

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
        private BlocCompetenceRepository $blocCompetenceRepository,
        private CompetenceRepository $competenceRepository,
        private ParcoursRepository $parcoursRepository,
        private EntityManagerInterface $entityManager,
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

    public function deplacerBlocCompetence(BlocCompetence $blocCompetence, string $sens)
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

    public function deplacerCompetence(Competence $competence, string $sens)
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
}
