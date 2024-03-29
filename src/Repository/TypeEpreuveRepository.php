<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/TypeEpreuveRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 25/02/2023 23:24
 */

namespace App\Repository;

use App\Entity\TypeDiplome;
use App\Entity\TypeEpreuve;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeEpreuve>
 *
 * @method TypeEpreuve|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeEpreuve|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeEpreuve[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeEpreuveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeEpreuve::class);
    }

    public function findAll(): array
    {
        return $this->findBy([], ['libelle' => 'ASC']);
    }

    public function save(TypeEpreuve $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

   public function findBySearchAndSort(string $sort, string $direction, ?string $q = ''): array
    {
        $qb = $this->createQueryBuilder('t')
            ->orderBy('t.' . $sort, $direction);

        if ('' !== $q) {
            $qb->andWhere('t.libelle LIKE :q')
                ->setParameter('q', '%' . $q . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function remove(TypeEpreuve $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByTypeDiplome(TypeDiplome $typeDiplome): array
    {
        $typeDiplomes = $typeDiplome->getTypeEpreuves();
        $tab = [];

        //filtre selon le type de diplome
        foreach ($typeDiplomes as $td) {
            $tab[$td->getId()] = $td;
        }

        return $tab;
    }
}
