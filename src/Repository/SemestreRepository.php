<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/SemestreRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 25/01/2023 20:31
 */

namespace App\Repository;

use App\Entity\Parcours;
use App\Entity\Semestre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Semestre>
 *
 * @method Semestre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Semestre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Semestre[]    findAll()
 * @method Semestre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SemestreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Semestre::class);
    }

    public function save(Semestre $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Semestre $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByParcoursOrdre(?int $ordreDestination, Parcours $parcours): ?Semestre
    {
        return $this->createQueryBuilder('s')
            ->join('s.parcours', 'p')
            ->where('p.id = :parcours')
            ->andWhere('s.ordre = :ordre')
            ->setParameter('parcours', $parcours->getId())
            ->setParameter('ordre', $ordreDestination)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
