<?php

namespace App\Repository;

use App\Entity\UeMutualisable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UeMutualisable>
 *
 * @method UeMutualisable|null find($id, $lockMode = null, $lockVersion = null)
 * @method UeMutualisable|null findOneBy(array $criteria, array $orderBy = null)
 * @method UeMutualisable[]    findAll()
 * @method UeMutualisable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UeMutualisableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UeMutualisable::class);
    }

    public function save(UeMutualisable $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UeMutualisable $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return UeMutualisable[] Returns an array of UeMutualisable objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UeMutualisable
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
