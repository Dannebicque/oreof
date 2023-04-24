<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/ParcoursRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 02/02/2023 18:32
 */

namespace App\Repository;

use App\Entity\AnneeUniversitaire;
use App\Entity\Formation;
use App\Entity\Mention;
use App\Entity\Parcours;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Parcours>
 *
 * @method Parcours|null find($id, $lockMode = null, $lockVersion = null)
 * @method Parcours|null findOneBy(array $criteria, array $orderBy = null)
 * @method Parcours[]    findAll()
 * @method Parcours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParcoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parcours::class);
    }

    public function save(Parcours $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Parcours $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByFormation(Formation $formation): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.formation = :formation')
            ->setParameter('formation', $formation)
            ->orderBy('p.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findParcours(AnneeUniversitaire $anneeUniversitaire, array $options): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.libelle <> :libelle')
            ->setParameter('libelle', Parcours::PARCOURS_DEFAUT)
            ->innerJoin('p.formation', 'f')
            ->andWhere('f.anneeUniversitaire = :annee')
            ->setParameter('annee', $anneeUniversitaire);

        foreach ($options as $sort => $direction) {
            if ($sort === 'recherche' && $direction !== '') {
                $qb->andWhere('p.libelle LIKE :recherche OR p.sigle LIKE :recherche')
                    ->setParameter('recherche', '%' . $direction . '%');
            } elseif ($sort === 'composante') {
                $qb->innerJoin('f.composantePorteuse', 'c')
                    ->addOrderBy('c.libelle', $direction);
            } elseif ($sort === 'mention') {
                $qb->leftJoin(Mention::class, 'm', 'WITH', 'f.mention = m.id');
                $qb->addOrderBy(
                    'CASE
                            WHEN f.mention IS NOT NULL THEN m.libelle
                            WHEN f.mentionTexte IS NOT NULL THEN f.mentionTexte
                            ELSE f.mentionTexte
                            END',
                    $direction
                );
            } else {
                $qb->addOrderBy('p.' . $sort, $direction);
            }
        }

        return $qb->getQuery()->getResult();
    }

    public function findRespOtherParcoursInFormation(Parcours $parcours, User $user): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.formation', 'f')
            ->andWhere('f = :formation')
            ->andWhere('p.respParcours = :user')
            ->andWhere('p.id <> :parcours')
            ->setParameter('formation', $parcours->getFormation())
            ->setParameter('user', $user)
            ->setParameter('parcours', $parcours)
            ->getQuery()
            ->getResult();
    }
}
