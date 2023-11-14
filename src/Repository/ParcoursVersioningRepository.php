<?php

namespace App\Repository;

use App\Entity\ParcoursVersioning;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ParcoursVersioning>
 *
 * @method ParcoursVersioning|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParcoursVersioning|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParcoursVersioning[]    findAll()
 * @method ParcoursVersioning[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParcoursVersioningRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParcoursVersioning::class);
    }

//    /**
//     * @return ParcoursVersioning[] Returns an array of ParcoursVersioning objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ParcoursVersioning
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
