<?php

namespace App\Repository;

use App\Entity\Semestre;
use App\Entity\ValidationIssue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ValidationIssue>
 */
class ValidationIssueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ValidationIssue::class);
    }

    public function deleteBySemestre(Semestre $semestre): void
    {
        $this->createQueryBuilder('vi')
            ->delete()
            ->where('vi.semestre = :semestre')
            ->setParameter('semestre', $semestre)
            ->getQuery()
            ->execute();
    }

    public function findBySemestre(?int $getId): array
    {
        return $this->createQueryBuilder('vi')
            ->where('vi.semestre = :id')
            ->setParameter('id', $getId)
            ->getQuery()
            ->getResult();
    }
}
