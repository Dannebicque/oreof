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

    public function deleteAll(): void
    {
        $this->getEntityManager()->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        $this->getEntityManager()->createQuery('DELETE FROM App\Entity\UserProfil')->execute();
        $this->getEntityManager()->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function findByComposanteEnableBySearch(CampagneCollecte $getCampagneCollecte, $composante, float|bool|int|string|null $q, float|bool|int|string|null $sort, float|bool|int|string|null $direction)
    {//todo: comment filtrer par composante
        return $this->createQueryBuilder('up')
            ->join('up.profil', 'p')
            ->join('up.user', 'u')
            ->where('u.isEnable = :isEnable')
            ->andWhere('u.isDeleted = false')
            ->andWhere('up.campagneCollecte = :campagne OR up.campagneCollecte IS NULL')
            ->setParameter('campagne', $getCampagneCollecte)
            ->setParameter('isEnable', true)
            // ->setParameter('composante', $composante)
            //   ->setParameter('q', '%' . $q . '%')
            ->addOrderBy('u.' . $sort, $direction)
            ->getQuery()
            ->getResult();
    }

    public function findByComposanteEnable(CampagneCollecte $campagneCollecte, $composante, float|bool|int|string|null $sort, float|bool|int|string|null $direction): array
    {

        //je veux une requete qui va récupérer les utilsateurs qui sont dans la bonne composante. Cela va dépendre du centre, si composante alors c'est une égalité, si c'est formation alors c'est la composante porteuse de la formation, si c'est le parcours alors c'est la composante porteuse de la formation du parcours, si c'est établissement pas concerné

        $qb = $this->createQueryBuilder('up')
            ->join('up.profil', 'p')
            ->join('up.user', 'u')
            ->leftJoin('up.formation', 'f')
            ->leftJoin('up.parcours', 'pa')
            ->leftJoin('pa.formation', 'pf')
            ->where('u.isEnable = :isEnable')
            ->andWhere('u.isDeleted = false')
            ->andWhere('up.campagneCollecte = :campagne OR up.campagneCollecte IS NULL')
            ->andWhere('
            up.composante = :composante
            OR f.composantePorteuse = :composante
            OR pf.composantePorteuse = :composante
        ')
            ->setParameter('campagne', $campagneCollecte)
            ->setParameter('isEnable', true)
            ->setParameter('composante', $composante)
            ->addOrderBy('u.' . $sort, $direction);

        return $qb->getQuery()->getResult();

    }
}
