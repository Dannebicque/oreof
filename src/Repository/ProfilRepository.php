<?php

namespace App\Repository;

use App\Entity\Profil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Profil>
 */
class ProfilRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Profil::class);
    }

    public function deleteAll()
    {
        $this->getEntityManager()->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        $this->getEntityManager()->createQuery('DELETE FROM App\Entity\Profil')->execute();
        $this->getEntityManager()->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
