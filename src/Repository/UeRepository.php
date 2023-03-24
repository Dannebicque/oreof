<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/UeRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 25/01/2023 20:32
 */

namespace App\Repository;

use App\Entity\Semestre;
use App\Entity\Ue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ue>
 *
 * @method Ue|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ue|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ue[]    findAll()
 * @method Ue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ue::class);
    }

    public function save(Ue $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Ue $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findBySemestreOrdre(?int $ordreDestination, ?Semestre $getSemestre): ?Ue
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.semestre = :semestre')
            ->andWhere('u.ordre = :ordre')
            ->setParameter('semestre', $getSemestre)
            ->setParameter('ordre', $ordreDestination)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBySemestreSubOrdre(?int $ordreDestination, ?Semestre $getSemestre, int $ordreUe): ?Ue
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.semestre = :semestre')
            ->andWhere('u.ordre = :ordre')
            ->andWhere('u.subOrdre = :subOrdre')
            ->setParameter('semestre', $getSemestre)
            ->setParameter('ordre', $ordreUe)
            ->setParameter('subOrdre', $ordreDestination)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getMaxOrdre(Semestre $semestre): int
    {
        return $this->createQueryBuilder('u')
            ->select('MAX(u.ordre)')
            ->andWhere('u.semestre = :semestre')
            ->setParameter('semestre', $semestre)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }
}
