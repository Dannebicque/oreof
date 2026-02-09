<?php

namespace App\Repository;

use App\Entity\Parcours;
use App\Entity\ParcoursTabState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ParcoursTabState>
 */
class ParcoursTabStateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParcoursTabState::class);
    }

    public function getOrCreate(Parcours $parcours, string $tabKey): ParcoursTabState
    {
        $state = $this->findOneBy(['parcours' => $parcours, 'tabKey' => $tabKey]);
        if ($state) {
            return $state;
        }

        $state = new ParcoursTabState($parcours, $tabKey);
        $this->getEntityManager()->persist($state);

        return $state;
    }

    /** @return array<string, ParcoursTabState> */
    public function indexByTabKey(Parcours $parcours): array
    {
        $states = $this->findBy(['parcours' => $parcours]);
        $map = [];
        foreach ($states as $s) {
            $map[$s->getTabKey()] = $s;
        }

        return $map;
    }
}
