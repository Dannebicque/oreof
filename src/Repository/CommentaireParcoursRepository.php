<?php

namespace App\Repository;

use App\Entity\CommentaireParcours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<CommentaireParcours>
 *
 * @method CommentaireParcours|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommentaireParcours|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommentaireParcours[]    findAll()
 * @method CommentaireParcours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentaireParcoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommentaireParcours::class);
    }
    public function findByZone(int $id, string $zone): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.parcours = :id')
            ->andWhere('c.zone = :zone')
            ->setParameter('id', $id)
            ->setParameter('zone', $zone)
            ->orderBy('c.created', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByZoneUser(int $id, string $zone, UserInterface $user): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.parcours = :id')
            ->andWhere('c.zone = :zone')
            ->andWhere('c.user = :user')
            ->setParameter('id', $id)
            ->setParameter('zone', $zone)
            ->setParameter('user', $user)
            ->orderBy('c.created', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByUser(?UserInterface $getUser): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.parcours', 'p')
            ->andWhere('c.user = :user')
            ->setParameter('user', $getUser)
            ->orderBy('p.libelle', 'DESC')
            ->addOrderBy('c.created', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
