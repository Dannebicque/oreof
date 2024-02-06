<?php

namespace App\Repository;

use App\Entity\DpeDemande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DpeDemande>
 *
 * @method DpeDemande|null find($id, $lockMode = null, $lockVersion = null)
 * @method DpeDemande|null findOneBy(array $criteria, array $orderBy = null)
 * @method DpeDemande[]    findAll()
 * @method DpeDemande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DpeDemandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DpeDemande::class);
    }

//    /**
//     * @return DpeDemande[] Returns an array of DpeDemande objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DpeDemande
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
