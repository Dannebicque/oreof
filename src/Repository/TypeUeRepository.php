<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/TypeUeRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 25/02/2023 23:24
 */

namespace App\Repository;

use App\Entity\TypeUe;
use App\TypeDiplome\Source\TypeDiplomeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeUe>
 *
 * @method TypeUe|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeUe|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeUe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeUeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeUe::class);
    }

    public function findAll(): array
    {
        return $this->findBy([], ['libelle' => 'ASC']);
    }

    public function save(TypeUe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TypeUe $entity, bool $flush = false): void
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
                $tab[$td->getLibelle()] = $td->getId();
            }
        }

        return $tab;
    }


}
