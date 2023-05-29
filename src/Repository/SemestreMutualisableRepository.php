<?php

namespace App\Repository;

use App\Entity\Composante;
use App\Entity\Formation;
use App\Entity\Mention;
use App\Entity\Parcours;
use App\Entity\SemestreMutualisable;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SemestreMutualisable>
 *
 * @method SemestreMutualisable|null find($id, $lockMode = null, $lockVersion = null)
 * @method SemestreMutualisable|null findOneBy(array $criteria, array $orderBy = null)
 * @method SemestreMutualisable[]    findAll()
 * @method SemestreMutualisable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SemestreMutualisableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SemestreMutualisable::class);
    }

    public function save(SemestreMutualisable $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SemestreMutualisable $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllBy(array $options): array
    {
        $qb = $this->createQueryBuilder('s')
            ->join('s.semestre', 'sem');


        foreach ($options as $sort => $direction) {
            if ($sort === 'formation') {
                $qb
                    ->join('sem.semestreParcours', 'sp')
                    ->leftJoin(Parcours::class, 'p', 'WITH', 'sp.parcours = p.id')
                    ->leftJoin(Formation::class, 'fo', 'WITH', 'p.formation = fo.id')
                    ->leftJoin(Mention::class, 'm', 'WITH', 'fo.mention = m.id')
                    ->addOrderBy(
                        'CASE
                            WHEN fo.mention IS NOT NULL THEN m.libelle
                            WHEN fo.mentionTexte IS NOT NULL THEN fo.mentionTexte
                            ELSE fo.mentionTexte
                            END',
                        $direction
                    );
            } elseif ($sort === 'composante') {
                $qb->leftJoin(Parcours::class, 'p', 'WITH', 's.parcours = p.id')
                    ->innerJoin(Formation::class, 'fo', 'WITH', 'p.formation = fo.id')
                    ->innerJoin(Composante::class, 'co', 'WITH', 'fo.composantePorteuse = co.id')
                    ->addOrderBy('co.libelle', $direction);
            } elseif ($sort === 'mention') {
                $qb->leftJoin(Parcours::class, 'p', 'WITH', 's.parcours = p.id')
                    ->leftJoin(Formation::class, 'fo', 'WITH', 'p.formation = fo.id')
                    ->leftJoin(Mention::class, 'm', 'WITH', 'fo.mention = m.id')
                    ->addOrderBy(
                        'CASE
                            WHEN fo.mention IS NOT NULL THEN m.libelle
                            WHEN fo.mentionTexte IS NOT NULL THEN fo.mentionTexte
                            ELSE fo.mentionTexte
                            END',
                        $direction
                    );
            } elseif ($sort === 'parcours') {
                $qb->leftJoin(Parcours::class, 'p', 'WITH', 's.parcours = p.id')
                    ->addOrderBy('p.libelle', $direction);
            } else {
                $qb->addOrderBy('sem.' . $sort, $direction);
            }
        }

        return $qb->getQuery()->getResult();
    }


    public function findByParcours(User $user, array $options): array
    {
        $qb = $this->createQueryBuilder('s')
            ->join('s.semestre', 'sem');


        foreach ($options as $sort => $direction) {
            if ($sort === 'formation') {
                $qb
                    ->join('sem.semestreParcours', 'sp')
                    ->leftJoin(Parcours::class, 'p', 'WITH', 'sp.parcours = p.id')
                    ->leftJoin(Formation::class, 'fo', 'WITH', 'p.formation = fo.id')
                    ->leftJoin(Mention::class, 'm', 'WITH', 'fo.mention = m.id')
                    ->addOrderBy(
                        'CASE
                            WHEN fo.mention IS NOT NULL THEN m.libelle
                            WHEN fo.mentionTexte IS NOT NULL THEN fo.mentionTexte
                            ELSE fo.mentionTexte
                            END',
                        $direction
                    );
            } elseif ($sort === 'composante') {
                $qb->leftJoin(Parcours::class, 'p', 'WITH', 's.parcours = p.id')
                    ->innerJoin(Formation::class, 'fo', 'WITH', 'p.formation = fo.id')
                    ->innerJoin(Composante::class, 'co', 'WITH', 'fo.composantePorteuse = co.id')
                    ->addOrderBy('co.libelle', $direction);
            } elseif ($sort === 'mention') {
                $qb->leftJoin(Parcours::class, 'p', 'WITH', 's.parcours = p.id')
                    ->leftJoin(Formation::class, 'fo', 'WITH', 'p.formation = fo.id')
                    ->leftJoin(Mention::class, 'm', 'WITH', 'fo.mention = m.id')
                    ->addOrderBy(
                        'CASE
                            WHEN fo.mention IS NOT NULL THEN m.libelle
                            WHEN fo.mentionTexte IS NOT NULL THEN fo.mentionTexte
                            ELSE fo.mentionTexte
                            END',
                        $direction
                    );
            } elseif ($sort === 'parcours') {
                $qb->leftJoin(Parcours::class, 'p', 'WITH', 's.parcours = p.id')
                    ->addOrderBy('p.libelle', $direction);
            } else {
                $qb->addOrderBy('sem.' . $sort, $direction);
            }
        }

        return $qb->getQuery()->getResult();
    }
}
