<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/CompetenceRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 23/02/2023 16:43
 */

namespace App\Repository;

use App\Entity\BlocCompetence;
use App\Entity\Competence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Competence>
 *
 * @method Competence|null find($id, $lockMode = null, $lockVersion = null)
 * @method Competence|null findOneBy(array $criteria, array $orderBy = null)
 * @method Competence[]    findAll()
 * @method Competence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompetenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Competence::class);
    }

    public function save(Competence $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Competence $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getMaxOrdreBlocCompetence(BlocCompetence $bcc): ?int
    {
        return $this->createQueryBuilder('c')
            ->select('MAX(c.ordre)')
            ->andWhere('c.blocCompetence = :bcc')
            ->setParameter('bcc', $bcc)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function decaleCompetence(BlocCompetence $bcc, int $ordre): void
    {
        $competences = $this->createQueryBuilder('c')
            ->andWhere('c.blocCompetence = :bcc')
            ->andWhere('c.ordre >= :ordre')
            ->setParameter('bcc', $bcc)
            ->setParameter('ordre', $ordre)
            ->getQuery()
            ->getResult();

        foreach ($competences as $competence) {
            $competence->setOrdre($competence->getOrdre() + 1);
            $competence->genereCode();
            $this->save($competence, true);
        }
    }
}
