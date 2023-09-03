<?php

namespace App\Repository;

use App\Entity\ButApprentissageCritique;
use App\Entity\ButCompetence;
use App\Entity\Semestre;
use App\Entity\Ue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ButApprentissageCritique>
 *
 * @method ButApprentissageCritique|null find($id, $lockMode = null, $lockVersion = null)
 * @method ButApprentissageCritique|null findOneBy(array $criteria, array $orderBy = null)
 * @method ButApprentissageCritique[]    findAll()
 * @method ButApprentissageCritique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ButApprentissageCritiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ButApprentissageCritique::class);
    }

    public function save(ButApprentissageCritique $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ButApprentissageCritique $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByCompetenceSemestre(ButCompetence $competence, Semestre $semestre): array
    {
        if ($semestre->getOrdre() === 1 || $semestre->getOrdre() === 2) {
            $annee = 'BUT1';
        } else  if ($semestre->getOrdre() === 3 || $semestre->getOrdre() === 4) {
            $annee = 'BUT2';
        } else {
            $annee = 'BUT3';
        }

        return $this->createQueryBuilder('ac')
            ->innerJoin('ac.niveau', 'an')
            ->innerJoin('an.competence', 'comp')
            ->where('an.annee = :annee')
            ->andWhere('comp.id = :competence')
            ->setParameter('annee', $annee)
            ->setParameter('competence', $competence->getId())
            ->getQuery()
            ->getResult();
    }
}
