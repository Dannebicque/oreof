<?php

namespace App\Repository;

use App\Entity\FicheMatiereMutualisable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FicheMatiereMutualisable>
 *
 * @method FicheMatiereMutualisable|null find($id, $lockMode = null, $lockVersion = null)
 * @method FicheMatiereMutualisable|null findOneBy(array $criteria, array $orderBy = null)
 * @method FicheMatiereMutualisable[]    findAll()
 * @method FicheMatiereMutualisable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FicheMatiereMutualisableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FicheMatiereMutualisable::class);
    }

    public function save(FicheMatiereMutualisable $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FicheMatiereMutualisable $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllBy(array $options, string|null $q)
    {
        $qb = $this->createQueryBuilder('f')
            ->join('f.ficheMatiere', 'fm');

        if ($q) {
            $qb->andWhere('fm.libelle LIKE :q')
                ->setParameter('q', '%' . $q . '%');
        }

        foreach ($options as $sort => $direction) {
            $qb->addOrderBy('fm.' . $sort, $direction);
        }

        return $qb->getQuery()->getResult();
    }
}
