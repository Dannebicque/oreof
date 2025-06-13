<?php

namespace App\Repository;

use App\Entity\CampagneCollecte;
use App\Entity\UserProfil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserProfil>
 */
class UserProfilRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserProfil::class);
    }

    public function findEnable(
        CampagneCollecte           $campagneCollecte,
        float|bool|int|string|null $sort,
        string|null                $direction
    ): array
    {
        return $this->createQueryBuilder('up')
            ->join('up.profil', 'p')
            ->join('up.user', 'u')
            ->where('u.isEnable = :isEnable')
            ->andWhere('u.isDeleted = false')
            ->andWhere('up.campagneCollecte = :campagne OR up.campagneCollecte IS NULL')
            ->setParameter('campagne', $campagneCollecte)
            ->setParameter('isEnable', true)
            ->addOrderBy('u.' . $sort, $direction)
            ->getQuery()
            ->getResult();
    }

    public function findEnableBySearch(
        CampagneCollecte           $campagneCollecte,
        string|null                $q,
        float|bool|int|string|null $sort,
        string|null                $direction
    )
    {
        return $this->createQueryBuilder('up')
            ->join('up.profil', 'p')
            ->join('up.user', 'u')
            ->where('u.isEnable = :isEnable')
            ->andWhere('u.nom LIKE :q OR u.prenom LIKE :q OR u.email LIKE :q OR u.username LIKE :q')
            ->andWhere('u.isDeleted = false')
            ->andWhere('up.campagneCollecte = :campagneCollecte OR up.campagneCollecte IS NULL')
            ->setParameter('campagneCollecte', $campagneCollecte)
            ->setParameter('isEnable', true)
            ->setParameter('q', '%' . $q . '%')
            ->addOrderBy('u.' . $sort, $direction)
            ->getQuery()
            ->getResult();
    }

    public function deleteAll()
    {
        $this->getEntityManager()->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        $this->getEntityManager()->createQuery('DELETE FROM App\Entity\UserProfil')->execute();
        $this->getEntityManager()->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
