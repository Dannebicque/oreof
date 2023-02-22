<?php

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

    public function findAll()
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

//    /**
//     * @return RythmeFormation[] Returns an array of RythmeFormation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RythmeFormation
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
