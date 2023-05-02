<?php

namespace App\Repository;

use App\Entity\Composante;
use App\Entity\FicheMatiereMutualisable;
use App\Entity\Formation;
use App\Entity\Mention;
use App\Entity\Parcours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<FicheMatiereMutualisable>
 *
 * @method FicheMatiereMutualisable|null find($id, $lockMode = null, $lockVersion = null)
 * @method FicheMatiereMutualisable|null findOneBy(array $criteria, array $orderBy = null)
 * @method FicheMatiereMutualisable[]    findAll()
 * @method FicheMatiereMutualisable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FicheMatiereMutualisableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FicheMatiereMutualisable::class);
    }

    public function save(FicheMatiereMutualisable $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FicheMatiereMutualisable $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllBy(array $options, string|null $q): array
    {
        $qb = $this->createQueryBuilder('f')
            ->join('f.ficheMatiere', 'fm');

        if ($q) {
            $qb->andWhere('fm.libelle LIKE :q')
                ->setParameter('q', '%' . $q . '%');
        }

        foreach ($options as $sort => $direction) {
            if ($sort === 'mention') {
                $qb->leftJoin(Parcours::class, 'p', 'WITH', 'fm.parcours = p.id')
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
                $qb->leftJoin(Parcours::class, 'p', 'WITH', 'f.parcours = p.id')
                    ->innerJoin(Formation::class, 'fo', 'WITH', 'p.formation = fo.id')
                    ->innerJoin(Composante::class, 'co', 'WITH', 'fo.composantePorteuse = co.id')
                    ->addOrderBy('co.libelle', $direction);
            } elseif ($sort === 'mentionmutualisable') {
                $qb->leftJoin(Parcours::class, 'p', 'WITH', 'f.parcours = p.id')
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
            }elseif ($sort === 'parcours') {
                $qb->leftJoin(Parcours::class, 'p', 'WITH', 'f.parcours = p.id')
                    ->addOrderBy('p.libelle', $direction);
            } else {
                $qb->addOrderBy('fm.' . $sort, $direction);
            }
        }

        return $qb->getQuery()->getResult();
    }

    public function findByParcours(
        ?UserInterface $user,
        array $options, string|null $q
    ) {
        //todo: ajouter les bons parcours uniquement...
        $qb = $this->createQueryBuilder('f')
            ->join('f.ficheMatiere', 'fm');

        if ($q) {
            $qb->andWhere('fm.libelle LIKE :q')
                ->setParameter('q', '%' . $q . '%');
        }

        foreach ($options as $sort => $direction) {
            if ($sort === 'mention') {
                $qb->leftJoin(Parcours::class, 'p', 'WITH', 'fm.parcours = p.id')
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
                $qb->leftJoin(Parcours::class, 'p', 'WITH', 'f.parcours = p.id')
                    ->innerJoin(Formation::class, 'fo', 'WITH', 'p.formation = fo.id')
                    ->innerJoin(Composante::class, 'co', 'WITH', 'fo.composantePorteuse = co.id')
                    ->addOrderBy('co.libelle', $direction);
            } elseif ($sort === 'mentionmutualisable') {
                $qb->leftJoin(Parcours::class, 'p', 'WITH', 'f.parcours = p.id')
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
            }elseif ($sort === 'parcours') {
                $qb->leftJoin(Parcours::class, 'p', 'WITH', 'f.parcours = p.id')
                    ->addOrderBy('p.libelle', $direction);
            } else {
                $qb->addOrderBy('fm.' . $sort, $direction);
            }
        }

        return $qb->getQuery()->getResult();
    }
}
