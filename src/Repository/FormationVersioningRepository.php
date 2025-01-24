<?php

namespace App\Repository;

use App\Entity\FormationVersioning;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormationVersioning>
 *
 * @method FormationVersioning|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormationVersioning|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormationVersioning[]    findAll()
 * @method FormationVersioning[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormationVersioningRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormationVersioning::class);
    }

//    /**
//     * @return FormationVersioning[] Returns an array of FormationVersioning objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FormationVersioning
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
