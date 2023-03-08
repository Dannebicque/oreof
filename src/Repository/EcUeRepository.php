<?php

namespace App\Repository;

use App\Classes\EcOrdre;
use App\Entity\EcUe;
use App\Entity\Ue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EcUe>
 *
 * @method EcUe|null find($id, $lockMode = null, $lockVersion = null)
 * @method EcUe|null findOneBy(array $criteria, array $orderBy = null)
 * @method EcUe[]    findAll()
 * @method EcUe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EcUeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EcUe::class);
    }

    public function save(EcUe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EcUe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUe(Ue $ue): array
    {
        return $this->createQueryBuilder('ecUe')
            ->join('ecUe.ec', 'ec')
            ->where('ecUe.ue = :ue')
            ->setParameter('ue', $ue)
            ->addOrderBy('ec.ordre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findLastEc(Ue $ue): ?array
    {
        return $this->createQueryBuilder('ecUe')
            ->join('ecUe.ec', 'ec')
            ->select('MAX(ec.ordre) as ordreMax')
            ->where('ecUe.ue = :ue')
            ->setParameter('ue', $ue)
            ->getQuery()
            ->getScalarResult();
    }

    public function findByUeOrdre(?int $ordreDestination, Ue $ue): ?EcUe
    {
        return $this->createQueryBuilder('ecUe')
            ->join('ecUe.ec', 'ec')
            ->where('ecUe.ue = :ue')
            ->andWhere('ec.ordre = :ordreDestination')
            ->setParameter('ue', $ue->getId())
            ->setParameter('ordreDestination', $ordreDestination)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
