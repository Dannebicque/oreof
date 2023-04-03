<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/BlocCompetenceRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Repository;

use App\Entity\BlocCompetence;
use App\Entity\Formation;
use App\Entity\Parcours;
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

    public function findByFormation(Formation $formation): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.formation = :formation')
            ->setParameter('formation', $formation)
            ->orderBy('b.ordre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByParcours(Parcours $parcours): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.parcours = :parcours')
            ->setParameter('parcours', $parcours)
            ->orderBy('b.ordre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getMaxOrdre(Formation $formation): ?int
    {
        return $this->createQueryBuilder('b')
            ->select('MAX(b.ordre)')
            ->andWhere('b.formation = :formation')
            ->setParameter('formation', $formation)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getMaxOrdreParcours(Parcours $parcours): ?int
    {
        return $this->createQueryBuilder('b')
            ->select('MAX(b.ordre)')
            ->andWhere('b.parcours = :parcours')
            ->setParameter('parcours', $parcours)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function decaleCompetence(Parcours $parcours, int $ordre): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.parcours = :parcours')
            ->andWhere('b.ordre >= :ordre')
            ->setParameter('parcours', $parcours)
            ->setParameter('ordre', $ordre)
            ->getQuery()
            ->getResult();
    }
}
