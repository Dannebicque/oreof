<?php

namespace App\Repository;

use App\Entity\Formation;
use App\Entity\Parcours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Parcours>
 *
 * @method Parcours|null find($id, $lockMode = null, $lockVersion = null)
 * @method Parcours|null findOneBy(array $criteria, array $orderBy = null)
 * @method Parcours[]    findAll()
 * @method Parcours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParcoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parcours::class);
    }

    public function save(Parcours $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Parcours $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByFormation(Formation $formation): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.formation = :formation')
            ->setParameter('formation', $formation)
            ->orderBy('p.libelle', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
