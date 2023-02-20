<?php

namespace App\Repository;

use App\Entity\TypeEpreuve;
use App\TypeDiplome\Source\TypeDiplomeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeEpreuve>
 *
 * @method TypeEpreuve|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeEpreuve|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeEpreuve[]    findAll()
 * @method TypeEpreuve[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeEpreuveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeEpreuve::class);
    }

    public function save(TypeEpreuve $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TypeEpreuve $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByTypeDiplome(TypeDiplomeInterface $typeDiplome): array
    {
        $typeDiplomes = $this->createQueryBuilder('t')
            ->orderBy('t.libelle', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        $tab = [];

        //filtre selon le type de diplome
        foreach ($typeDiplomes as $td) {
            if (in_array($typeDiplome::class, $td->getTypeDiplome())) {
                $tab[] = $td;
            }
        }

        return $tab;
    }
}
