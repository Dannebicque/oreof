<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/NatureUeEcRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 25/02/2023 23:24
 */

namespace App\Repository;

use App\Entity\NatureUeEc;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NatureUeEc>
 *
 * @method NatureUeEc|null find($id, $lockMode = null, $lockVersion = null)
 * @method NatureUeEc|null findOneBy(array $criteria, array $orderBy = null)
 * @method NatureUeEc[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NatureUeEcRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NatureUeEc::class);
    }

    public function findAll(): array
    {
        return $this->findBy([], ['libelle' => 'ASC']);
    }

    public function save(NatureUeEc $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(NatureUeEc $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByBuilder(string $type): QueryBuilder
    {
        return $this->createQueryBuilder('n')
            ->where('n.type = :type')
            ->setParameter('type', $type)
            ->orderBy('n.libelle', 'ASC');
    }
}
