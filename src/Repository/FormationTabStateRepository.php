<?php

namespace App\Repository;

use App\Entity\Formation;
use App\Entity\FormationTabState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormationTabState>
 */
class FormationTabStateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormationTabState::class);
    }

    public function getOrCreate(Formation $formation, string $tabKey): FormationTabState
    {
        $state = $this->findOneBy(['formation' => $formation, 'tabKey' => $tabKey]);
        if ($state) {
            return $state;
        }

        $state = new FormationTabState($formation, $tabKey);
        $this->getEntityManager()->persist($state);

        return $state;
    }

    /** @return array<string, FormationTabState> */
    public function indexByTabKey(Formation $formation): array
    {
        $states = $this->findBy(['formation' => $formation]);
        $map = [];
        foreach ($states as $s) {
            $map[$s->getTabKey()] = $s;
        }
        return $map;
    }
}
