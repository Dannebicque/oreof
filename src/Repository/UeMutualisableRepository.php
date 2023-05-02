<?php

namespace App\Repository;

use App\Entity\Composante;
use App\Entity\Formation;
use App\Entity\Mention;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\TypeUe;
use App\Entity\UeMutualisable;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UeMutualisable>
 *
 * @method UeMutualisable|null find($id, $lockMode = null, $lockVersion = null)
 * @method UeMutualisable|null findOneBy(array $criteria, array $orderBy = null)
 * @method UeMutualisable[]    findAll()
 * @method UeMutualisable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UeMutualisableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UeMutualisable::class);
    }

    public function save(UeMutualisable $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UeMutualisable $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllBy(array $options): array
    {
        $qb = $this->createQueryBuilder('u')
            ->join('u.ue', 'ue');


        foreach ($options as $sort => $direction) {
            if ($sort === 'formation') {
                $qb->leftJoin(Semestre::class, 's', 'WITH', 'ue.semestre = s.id')
                    ->join('s.semestreParcours', 'sp')
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
                $qb->leftJoin(Parcours::class, 'p', 'WITH', 'u.parcours = p.id')
                    ->innerJoin(Formation::class, 'fo', 'WITH', 'p.formation = fo.id')
                    ->innerJoin(Composante::class, 'co', 'WITH', 'fo.composantePorteuse = co.id')
                    ->addOrderBy('co.libelle', $direction);
            } elseif ($sort === 'mention') {
                $qb->leftJoin(Parcours::class, 'p', 'WITH', 'u.parcours = p.id')
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
                $qb->leftJoin(Parcours::class, 'p', 'WITH', 'u.parcours = p.id')
                    ->addOrderBy('p.libelle', $direction);
            } elseif ($sort === 'typeUe') {
                $qb->leftJoin(TypeUe::class, 'tu', 'WITH', 'ue.typeUe = tu.id')
                    ->addOrderBy('tu.libelle', $direction);
            } else {
                $qb->addOrderBy('ue.' . $sort, $direction);
            }
        }

        return $qb->getQuery()->getResult();
    }

    public function findByParcours(User $user, array $options): array
    {
        //todo: filtrer selon les parcours de l'utilisateur
        $qb = $this->createQueryBuilder('u')
            ->join('u.ue', 'ue');


        foreach ($options as $sort => $direction) {
            if ($sort === 'formation') {
                $qb->leftJoin(Semestre::class, 's', 'WITH', 'ue.semestre = s.id')
                    ->join('s.semestreParcours', 'sp')
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
                $qb->leftJoin(Parcours::class, 'p', 'WITH', 'u.parcours = p.id')
                    ->innerJoin(Formation::class, 'fo', 'WITH', 'p.formation = fo.id')
                    ->innerJoin(Composante::class, 'co', 'WITH', 'fo.composantePorteuse = co.id')
                    ->addOrderBy('co.libelle', $direction);
            } elseif ($sort === 'mention') {
                $qb->leftJoin(Parcours::class, 'p', 'WITH', 'u.parcours = p.id')
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
                $qb->leftJoin(Parcours::class, 'p', 'WITH', 'u.parcours = p.id')
                    ->addOrderBy('p.libelle', $direction);
            } elseif ($sort === 'typeUe') {
                $qb->leftJoin(TypeUe::class, 'tu', 'WITH', 'ue.typeUe = tu.id')
                    ->addOrderBy('tu.libelle', $direction);
            } else {
                $qb->addOrderBy('ue.' . $sort, $direction);
            }
        }

        return $qb->getQuery()->getResult();
    }
}
