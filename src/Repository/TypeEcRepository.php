<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/TypeEcRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 25/02/2023 23:24
 */

namespace App\Repository;

use App\Entity\Formation;
use App\Entity\TypeDiplome;
use App\Entity\TypeEc;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeEc>
 *
 * @method TypeEc|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeEc|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeEc[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeEcRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeEc::class);
    }

    public function findAll(): array
    {
        return $this->findBy([], ['libelle' => 'ASC']);
    }

    public function save(TypeEc $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TypeEc $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByTypeDiplome(TypeDiplome $typeDiplome): QueryBuilder
    {
        return $this->createQueryBuilder('t')
            ->join('t.typeDiplomes', 'td')
            ->where('td.id = :typeDiplome')
            ->setParameter('typeDiplome', $typeDiplome->getId())
            ->orderBy('t.libelle', 'ASC');
    }

    public function findByTypeDiplomeAndFormation(TypeDiplome $typeDiplome, Formation $formation): QueryBuilder
    {
        return $this->createQueryBuilder('t')
            ->join('t.typeDiplomes', 'td')
            ->where('td.id = :typeDiplome')
            ->andWhere('t.formation = :formation')
            ->orWhere('t.formation IS NULL')
            ->setParameter('typeDiplome', $typeDiplome->getId())
            ->setParameter('formation', $formation)
            ->orderBy('t.libelle', 'ASC');
    }


}
