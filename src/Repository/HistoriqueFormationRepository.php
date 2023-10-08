<?php

namespace App\Repository;

use App\Entity\Formation;
use App\Entity\HistoriqueFormation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HistoriqueFormation>
 *
 * @method HistoriqueFormation|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistoriqueFormation|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistoriqueFormation[]    findAll()
 * @method HistoriqueFormation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoriqueFormationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoriqueFormation::class);
    }

    public function save(HistoriqueFormation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(HistoriqueFormation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByFormationLastStep(Formation $formation, string $step): ?HistoriqueFormation
    {
        return $this->createQueryBuilder('h')
            ->where('h.formation = :formation')
            ->andWhere('h.etape = :step')
            ->setParameter('formation', $formation)
            ->setParameter('step', $step)
            ->orderBy('h.date', 'DESC')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
