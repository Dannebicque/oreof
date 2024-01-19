<?php

namespace App\Repository;

use App\Entity\FicheMatiereVersioning;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FicheMatiereVersioning>
 *
 * @method FicheMatiereVersioning|null find($id, $lockMode = null, $lockVersion = null)
 * @method FicheMatiereVersioning|null findOneBy(array $criteria, array $orderBy = null)
 * @method FicheMatiereVersioning[]    findAll()
 * @method FicheMatiereVersioning[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FicheMatiereVersioningRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FicheMatiereVersioning::class);
    }

//    /**
//     * @return FicheMatiereVersioning[] Returns an array of FicheMatiereVersioning objects
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

//    public function findOneBySomeField($value): ?FicheMatiereVersioning
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
