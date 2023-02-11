<?php

namespace App\Repository;

use App\Entity\UserCentre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserCentre>
 *
 * @method UserCentre|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserCentre|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserCentre[]    findAll()
 * @method UserCentre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserCentreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCentre::class);
    }

    public function save(UserCentre $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserCentre $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByComposante(int $composante): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.composante = :composante')
            ->setParameter('composante', $composante)
            ->getQuery()
            ->getResult();
    }
}
