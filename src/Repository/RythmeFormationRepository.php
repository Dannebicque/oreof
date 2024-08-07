<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/RythmeFormationRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 25/02/2023 23:24
 */

namespace App\Repository;

use App\Entity\RythmeFormation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RythmeFormation>
 *
 * @method RythmeFormation|null find($id, $lockMode = null, $lockVersion = null)
 * @method RythmeFormation|null findOneBy(array $criteria, array $orderBy = null)
 * @method RythmeFormation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RythmeFormationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RythmeFormation::class);
    }

    public function findAll(): array
    {
        return $this->findBy([], ['libelle' => 'ASC']);
    }

    public function save(RythmeFormation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RythmeFormation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
