<?php

namespace App\Repository;

use App\Entity\CommentaireFicheMatiere;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<CommentaireFicheMatiere>
 *
 * @method CommentaireFicheMatiere|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommentaireFicheMatiere|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommentaireFicheMatiere[]    findAll()
 * @method CommentaireFicheMatiere[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentaireFicheMatiereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommentaireFicheMatiere::class);
    }
    public function findByZone(int $id, string $zone): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.ficheMatiere = :id')
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
            ->andWhere('c.ficheMatiere = :id')
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
            ->innerJoin('c.ficheMatiere', 'f')
            ->andWhere('c.user = :user')
            ->setParameter('user', $getUser)
            ->orderBy('f.libelle', 'DESC')
            ->addOrderBy('c.created', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
