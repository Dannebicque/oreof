<?php

namespace App\Repository;

use App\Entity\FicheMatiereTabState;
use App\Entity\FicheMatiere;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FicheMatiereTabState>
 */
class FicheMatiereTabStateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FicheMatiereTabState::class);
    }

    public function getOrCreate(FicheMatiere $ficheMatiere, string $tabKey): FicheMatiereTabState
    {
        $state = $this->findOneBy(['ficheMatiere' => $ficheMatiere, 'tabKey' => $tabKey]);
        if ($state) {
            return $state;
        }

        $state = new FicheMatiereTabState($ficheMatiere, $tabKey);
        $this->getEntityManager()->persist($state);

        return $state;
    }

    /** @return array<string, FicheMatiereTabState> */
    public function indexByTabKey(FicheMatiere $ficheMatiere): array
    {
        $states = $this->findBy(['ficheMatiere' => $ficheMatiere]);
        $map = [];
        foreach ($states as $s) {
            $map[$s->getTabKey()] = $s;
        }
        return $map;
    }
}
