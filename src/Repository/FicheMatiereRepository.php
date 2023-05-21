<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/ElementConstitutifRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 08/03/2023 11:26
 */

namespace App\Repository;

use App\Entity\AnneeUniversitaire;
use App\Entity\FicheMatiere;
use App\Entity\Formation;
use App\Entity\Mention;
use App\Entity\Parcours;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FicheMatiere>
 *
 * @method FicheMatiere|null find($id, $lockMode = null, $lockVersion = null)
 * @method FicheMatiere|null findOneBy(array $criteria, array $orderBy = null)
 * @method FicheMatiere[]    findAll()
 * @method FicheMatiere[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FicheMatiereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FicheMatiere::class);
    }

    public function save(FicheMatiere $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FicheMatiere $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByParcours(Parcours $parcours): array
    {
        //soit la fiche est portée par le parcours, soit elle est mutualisée avec ce parcours
        return $this->createQueryBuilder('f')
            ->leftJoin('f.parcours', 'p')
            ->leftJoin('f.ficheMatiereParcours', 'pm')
            ->where('p = :parcours')
            ->orWhere('pm = :parcours')
            ->setParameter('parcours', $parcours)
            ->orderBy('f.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByAdmin(
        AnneeUniversitaire $anneeUniversitaire,
        array $options = [],
        string|null $q = null
    ) {
        $qb = $this->createQueryBuilder('f')
            ->leftJoin(Parcours::class, 'p', 'WITH', 'f.parcours = p.id')
            ->join(Formation::class, 'fo', 'WITH', 'p.formation = fo.id')
            ->andWhere('fo.anneeUniversitaire = :annee')
            ->setParameter('annee', $anneeUniversitaire)
            ->leftJoin(User::class, 'u', 'WITH', 'f.responsableFicheMatiere = u.id');

        $this->addFiltres($q, $qb, $options);

        return $qb->getQuery()->getResult();
    }

    public function findByResponsableFicheMatiere(
        User $user,
        AnneeUniversitaire $anneeUniversitaire,
        array $options = [],
        string|null $q = null
    ): array {
        $qb = $this->createQueryBuilder('f')
            ->leftJoin(Parcours::class, 'p', 'WITH', 'f.parcours = p.id')
            ->join(Formation::class, 'fo', 'WITH', 'p.formation = fo.id')
            ->andWhere('fo.anneeUniversitaire = :annee')
            ->andWhere('f.responsableFicheMatiere = :user')
            ->setParameter('user', $user)
            ->setParameter('annee', $anneeUniversitaire);

        $this->addFiltres($q, $qb, $options);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string|null                $q
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param array                      $options
     *
     * @return void
     */
    private function addFiltres(?string $q, \Doctrine\ORM\QueryBuilder $qb, array $options): void
    {
        if (null !== $q) {
            $qb->andWhere('f.libelle LIKE :q')
                ->setParameter('q', '%' . $q . '%');
        }

        foreach ($options as $sort => $direction) {
            if ($sort === 'responsableFicheMatiere') {
                $qb->addOrderBy('u.nom', $direction);
                $qb->addOrderBy('u.prenom', $direction);
            } elseif ($sort === 'mention') {
                $qb->leftJoin(Formation::class, 'fo', 'WITH', 'p.formation = fo.id');
                $qb->leftJoin(Mention::class, 'm', 'WITH', 'fo.mention = m.id');
                $qb->addOrderBy(
                    'CASE
                            WHEN fo.mention IS NOT NULL THEN m.libelle
                            WHEN fo.mentionTexte IS NOT NULL THEN fo.mentionTexte
                            ELSE fo.mentionTexte
                            END',
                    $direction
                );
            } else {
                $qb->addOrderBy('f.' . $sort, $direction);
            }
        }
    }
}
