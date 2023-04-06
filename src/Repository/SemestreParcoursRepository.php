<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/SemestreParcoursRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/02/2023 13:44
 */

namespace App\Repository;

use App\Entity\Parcours;
use App\Entity\SemestreParcours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SemestreParcours>
 *
 * @method SemestreParcours|null find($id, $lockMode = null, $lockVersion = null)
 * @method SemestreParcours|null findOneBy(array $criteria, array $orderBy = null)
 * @method SemestreParcours[]    findAll()
 * @method SemestreParcours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SemestreParcoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SemestreParcours::class);
    }

    public function save(SemestreParcours $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SemestreParcours $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByParcours(Parcours $parcours): array
    {
        return $this->createQueryBuilder('sp')
            ->where('sp.parcours = :parcours')
            ->setParameter('parcours', $parcours)
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByParcoursOrdre(?int $ordreDestination, Parcours $parcours): ?SemestreParcours
    {
        return $this->createQueryBuilder('sp')
            ->where('sp.parcours = :parcours')
            ->andWhere('sp.ordre = :ordre')
            ->setParameter('parcours', $parcours)
            ->setParameter('ordre', $ordreDestination)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
