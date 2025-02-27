<?php

namespace App\Repository;

use App\Entity\ComposanteInformation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ComposanteInformation>
 *
 * @method ComposanteInformation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ComposanteInformation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ComposanteInformation[]    findAll()
 * @method ComposanteInformation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ComposanteInformationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ComposanteInformation::class);
    }

//    /**
//     * @return ComposanteInformation[] Returns an array of ComposanteInformation objects
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

//    public function findOneBySomeField($value): ?ComposanteInformation
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
