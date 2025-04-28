<?php

namespace App\Repository;

use App\Entity\Composante;
use App\Entity\DpeDemande;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Enums\EtatDpeEnum;
use App\Enums\TypeModificationDpeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DpeDemande>
 *
 * @method DpeDemande|null find($id, $lockMode = null, $lockVersion = null)
 * @method DpeDemande|null findOneBy(array $criteria, array $orderBy = null)
 * @method DpeDemande[]    findAll()
 * @method DpeDemande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DpeDemandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DpeDemande::class);
    }

    public function findLastOpenedDemande(Parcours $parcours, EtatDpeEnum $etat, ?TypeModificationDpeEnum $typeModificationDpeEnum = null): ?DpeDemande
    {
        $query = $this->createQueryBuilder('d')
            ->where('d.parcours = :parcours')
            ->andWhere('d.etatDemande = :etat')
            ->andWhere('d.dateCloture IS NULL')
            ->setParameter('parcours', $parcours)
            ->setParameter('etat', $etat);

        if (null !== $typeModificationDpeEnum) {
            $query->andWhere('d.niveauModification = :typeModificationDpeEnum')
                ->setParameter('typeModificationDpeEnum', $typeModificationDpeEnum);
        }

        return $query->getQuery()
            ->getOneOrNullResult();
    }

    public function findLastUnclosedDemande(Parcours $parcours): ?DpeDemande
    {
        return $this->createQueryBuilder('d')
            ->where('d.parcours = :parcours')
            ->andWhere('d.dateCloture IS NULL')
            ->setParameter('parcours', $parcours)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLastOpenedDemandeMention(Formation $formation, EtatDpeEnum $etat)
    {
        return $this->createQueryBuilder('d')
            ->where('d.formation = :formation')
            ->andWhere('d.parcours IS NULL')
            ->andWhere('d.etatDemande = :etat')
            ->andWhere('d.dateCloture IS NULL')
            ->setParameter('formation', $formation)
            ->setParameter('etat', $etat)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByComposante(Composante $composante): array
    {
        return $this->createQueryBuilder('d')
            ->innerJoin('d.parcours', 'p')
            ->innerJoin('p.formation', 'f')
            ->where('f.composantePorteuse = :composante')
            ->setParameter('composante', $composante)
            ->getQuery()
            ->getResult();
    }
}
