<?php

namespace App\Repository;

use App\Entity\TypeDiplome;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeDiplome>
 *
 * @method TypeDiplome|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeDiplome|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeDiplome[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeDiplomeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeDiplome::class);
    }

    public function findAll(): array
    {
        return $this->findBy([], ['libelle' => 'ASC']);
    }

    public function save(TypeDiplome $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TypeDiplome $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
