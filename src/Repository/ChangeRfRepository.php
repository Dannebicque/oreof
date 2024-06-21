<?php

namespace App\Repository;

use App\Entity\ChangeRf;
use App\Entity\Composante;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChangeRf>
 *
 * @method ChangeRf|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChangeRf|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChangeRf[]    findAll()
 * @method ChangeRf[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChangeRfRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChangeRf::class);
    }

    public function findByComposanteTypeValidation(Composante $composante, ?string $etat)
    {
        if ($etat === null || $etat === 'all') {
            $qb = $this->createQueryBuilder('c')
                ->innerJoin('c.formation', 'f')
                ->andWhere('f.composantePorteuse = :composante')
                ->setParameter('composante', $composante)
                ->orderBy('c.dateDemande', 'DESC')
            ;
        } else {
            $qb = $this->createQueryBuilder('c')
                ->innerJoin('c.formation', 'f')
                ->andWhere('f.composantePorteuse = :composante')
                ->setParameter('composante', $composante)
                ->andWhere('c.etatDemande = :typeValidation')
                ->setParameter('typeValidation', $etat)
                ->orderBy('c.dateDemande', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }
}
