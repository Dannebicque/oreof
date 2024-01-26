<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/ElementConstitutifRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 08/03/2023 11:26
 */

namespace App\Repository;

use App\Entity\Dpe;
use App\Entity\Composante;
use App\Entity\DpeParcours;
use App\Entity\FicheMatiere;
use App\Entity\Formation;
use App\Entity\Mention;
use App\Entity\Parcours;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

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
            ->orWhere('pm.parcours = :parcours')
            ->setParameter('parcours', $parcours)
            ->orderBy('f.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByComposante(Composante $composante): array
    {
        return $this->createQueryBuilder('f')
            ->join('f.composante', 'c')
            ->where('c = :composante')
            ->setParameter('composante', $composante)
            ->orderBy('f.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByAdmin(
        Dpe   $dpe,
        array $options = []
    ): array {
        $qb = $this->createQueryBuilder('f')
            ->leftJoin(Parcours::class, 'p', 'WITH', 'f.parcours = p.id')
            ->join(Formation::class, 'fo', 'WITH', 'p.formation = fo.id')
            ->join(DpeParcours::class, 'dp', 'WITH', 'p.id = dp.parcours')
            ->leftJoin(User::class, 'u', 'WITH', 'f.responsableFicheMatiere = u.id')
            ->andWhere('dp.dpe = :dpe')
            ->andWhere('f.horsDiplome = 0')
            ->orWhere('f.horsDiplome IS NULL')
            ->setParameter('dpe', $dpe);

        $this->addFiltres($qb, $options);

        return $qb->getQuery()->getResult();
    }

    public function countByAdmin(Dpe $dpe, array $options): ?int
    {
        $qb = $this->createQueryBuilder('f')
            ->leftJoin(Parcours::class, 'p', 'WITH', 'f.parcours = p.id')
            ->join(Formation::class, 'fo', 'WITH', 'p.formation = fo.id')
            ->join(DpeParcours::class, 'dp', 'WITH', 'p.id = dp.parcours')
            ->leftJoin(User::class, 'u', 'WITH', 'f.responsableFicheMatiere = u.id')
            ->andWhere('dp.dpe = :dpe')
            ->andWhere('f.horsDiplome = 0')
            ->orWhere('f.horsDiplome IS NULL')
            ->setParameter('dpe', $dpe);

        $this->addFiltres($qb, $options, true);

        return $qb->select('count(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

//    public function findByResponsableFicheMatiere(
//        UserInterface      $user,
//        AnneeUniversitaire $anneeUniversitaire,
//        array              $options = []
//    ): array {
//        $qb = $this->createQueryBuilder('f')
//            ->leftJoin(Parcours::class, 'p', 'WITH', 'f.parcours = p.id')
//            ->leftJoin(User::class, 'u', 'WITH', 'f.responsableFicheMatiere = u.id')
//            ->join(Formation::class, 'fo', 'WITH', 'p.formation = fo.id')
//            ->andWhere('fo.anneeUniversitaire = :annee')
//            ->andWhere('f.responsableFicheMatiere = :user')
//            ->setParameter('user', $user)
//            ->setParameter('annee', $anneeUniversitaire);
//
//        $this->addFiltres($qb, $options);
//
//        return $qb->getQuery()->getResult();
//    }

//    public function countByResponsableFicheMatiere(
//        UserInterface      $user,
//        AnneeUniversitaire $anneeUniversitaire,
//        array              $options = []
//    ): int {
//        $qb = $this->createQueryBuilder('f')
//            ->leftJoin(Parcours::class, 'p', 'WITH', 'f.parcours = p.id')
//            ->leftJoin(User::class, 'u', 'WITH', 'f.responsableFicheMatiere = u.id')
//            ->join(Formation::class, 'fo', 'WITH', 'p.formation = fo.id')
//            ->andWhere('fo.anneeUniversitaire = :annee')
//            ->andWhere('f.responsableFicheMatiere = :user')
//            ->setParameter('user', $user)
//            ->setParameter('annee', $anneeUniversitaire);
//
//        $this->addFiltres($qb, $options);
//
//        return $qb->select('count(f.id)')
//            ->getQuery()
//            ->getSingleScalarResult();
//    }

    private function addFiltres(QueryBuilder $qb, array $options, bool $count = false): void
    {
        $sort = $options['sort'] ?? 'mention';
        $direction = $options['direction'] ?? 'ASC';

        if (array_key_exists('parcours', $options) && null !== $options['parcours']) {
            $qb->andWhere('f.parcours = :parcours')
                ->setParameter('parcours', $options['parcours']);
        }

        if (array_key_exists('referent', $options) && null !== $options['referent']) {
            if ($options['referent'] === 'vide') {
                $qb->andWhere('f.responsableFicheMatiere IS NULL');
            } else {
                $qb->andWhere('f.responsableFicheMatiere = :referent')
                    ->setParameter('referent', $options['referent']);
            }
        }

        if (array_key_exists('q', $options) && null !== $options['q']) {
            $qb->andWhere('f.libelle LIKE :q')
                ->setParameter('q', '%' . $options['q'] . '%');
        }

        if (array_key_exists('libelle', $options) && null !== $options['libelle']) {
            $qb->andWhere('f.libelle LIKE :q')
                ->setParameter('q', '%' . $options['libelle'] . '%');
        }

        if ($sort === 'responsableFicheMatiere') {
            $qb->addOrderBy('u.nom', $direction);
            $qb->addOrderBy('u.prenom', $direction);
        } elseif ($sort === 'mention') {
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

        if ($count === false) {
            $start = $options['start'] ?? 0;

            $qb->setFirstResult($start * 50)
                ->setMaxResults($options['length'] ?? 50);
        }
    }

//    public function findByResponsableParcours(?UserInterface $user, AnneeUniversitaire $getAnneeUniversitaire, array $options): array
//    {
//        $query = $this->createQueryBuilder('f')
//            ->leftJoin('f.parcours', 'p')
//            ->join('p.formation', 'fo')
//            ->where('p.respParcours = :parcours')
//            ->orWhere('p.coResponsable = :parcours')
//            ->setParameter('parcours', $user)
//            ->orderBy('f.libelle', 'ASC');
//        $this->addFiltres($query, $options);
//
//        return $query->getQuery()
//            ->getResult();
//    }

//    public function findByResponsableFormation(?UserInterface $user, AnneeUniversitaire $getAnneeUniversitaire, array $options): array
//    {
//        $query = $this->createQueryBuilder('f')
//            ->leftJoin('f.parcours', 'p')
//            ->join('p.formation', 'fo')
//            ->where('fo.responsableMention = :parcours')
//            ->orWhere('fo.coResponsable = :parcours')
//            ->setParameter('parcours', $user)
//            ->orderBy('f.libelle', 'ASC');
//
//        $this->addFiltres($query, $options);
//
//        return $query->getQuery()
//            ->getResult();
//    }

    public function findByHd(Dpe $dpe, array $options): array
    {
        $start = $options['start'] ?? 0;
        $query = $this->createQueryBuilder('f')
            ->where('f.horsDiplome = 1')
            ->orderBy('f.libelle', 'ASC')
            ->setFirstResult($start * 50)
            ->setMaxResults($options['length'] ?? 50);

        //$this->addFiltres($query, $options);

        if (array_key_exists('q', $options) && null !== $options['q']) {
            $query->andWhere('f.libelle LIKE :q')
                ->setParameter('q', '%' . $options['q'] . '%');
        }

        return $query->getQuery()
            ->getResult();
    }

    public function findByCodeAndFormation(mixed $codeMatiere, Formation $formation): array
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.parcours', 'p')
            ->join('p.formation', 'fo')
            ->where('fo = :formation')
            ->andWhere('f.sigle = :code')
            ->setParameter('formation', $formation)
            ->setParameter('code', $codeMatiere)
            ->getQuery()
            ->getResult();
    }

    public function countByHd(Dpe $dpe, array $options): ?int
    {
        $query = $this->createQueryBuilder('f')
            ->select('count(f.id)')
            ->andWhere('f.horsDiplome = 1');

        if (array_key_exists('q', $options) && null !== $options['q']) {
            $query->andWhere('f.libelle LIKE :q')
                ->setParameter('q', '%' . $options['q'] . '%');
        }

       // $this->addFiltres($query, $all);

        return $query->getQuery()
            ->getSingleScalarResult();
    }

    public function findByResponsable(?UserInterface $user, Dpe $dpe, array $options): array
    {
        $query = $this->createQueryBuilder('f')
            ->leftJoin('f.parcours', 'p')
            ->join('p.formation', 'fo')
            ->join('p.dpeParcours', 'dp')
            ->orWhere('(fo.responsableMention = :parcours OR fo.coResponsable = :parcours)')
            ->orWhere('(p.respParcours = :parcours OR p.coResponsable = :parcours)')
            ->orWhere('f.responsableFicheMatiere = :user')
            ->andWhere('dp.dpe = :dpe') // Pour la troisième requête
            ->orderBy('f.libelle', 'ASC')
            ->setParameters([
                'parcours' => $user,
                'dpe' => $dpe,
                'user' => $user
            ]);

        $this->addFiltres($query, $options);

        return $query->getQuery()
            ->getResult();
    }

    public function countByResponsable(?UserInterface $user, Dpe $dpe, array $options): ?int
    {
        $query = $this->createQueryBuilder('f')
            ->leftJoin('f.parcours', 'p')
            ->join('p.formation', 'fo')
            ->join('p.dpeParcours', 'dp')
            // Pour la première requête
            ->andWhere('(fo.responsableMention = :parcours OR fo.coResponsable = :parcours)')
            // Pour la deuxième requête
            ->orWhere('(p.respParcours = :parcours OR p.coResponsable = :parcours)')
            ->andWhere('dp.dpe = :dpe') // Pour la troisième requête
            // Ajout condition à la derniere requete
            ->andWhere('f.responsableFicheMatiere = :user')
            ->orderBy('f.libelle', 'ASC')
            ->setParameters([
                'parcours' => $user,
                'dpe' => $dpe,
                'user' => $user
            ]);

        $this->addFiltres($query, $options, true);

        return $query->select('count(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
