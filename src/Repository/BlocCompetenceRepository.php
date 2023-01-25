<?php

namespace App\Repository;

use App\Entity\BlocCompetence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BlocCompetence>
 *
 * @method BlocCompetence|null find($id, $lockMode = null, $lockVersion = null)
 * @method BlocCompetence|null findOneBy(array $criteria, array $orderBy = null)
 * @method BlocCompetence[]    findAll()
 * @method BlocCompetence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlocCompetenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlocCompetence::class);
    }

    public function save(BlocCompetence $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BlocCompetence $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return BlocCompetence[] Returns an array of BlocCompetence objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?BlocCompetence
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
