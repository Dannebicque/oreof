<?php

namespace App\Repository;

use App\Entity\ChangeRf;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChangeRf>
 *
 * @method ChangeRf|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChangeRf|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChangeRf[]    findAll()
 * @method ChangeRf[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChangeRfRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChangeRf::class);
    }

//    /**
//     * @return ChangeRf[] Returns an array of ChangeRf objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ChangeRf
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
