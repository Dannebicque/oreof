<?php

namespace App\Repository;

use App\Entity\FormationDemande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormationDemande>
 *
 * @method FormationDemande|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormationDemande|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormationDemande[]    findAll()
 * @method FormationDemande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormationDemandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormationDemande::class);
    }

    public function save(FormationDemande $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FormationDemande $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
