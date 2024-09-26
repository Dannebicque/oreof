<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/ElementConstitutifRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 08/03/2023 11:26
 */

namespace App\Repository;

use App\Entity\CampagneCollecte;
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

    /** @deprecated('pas de sens?') */
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
        CampagneCollecte $campagneCollecte,
        array            $options = []
    ): array {
        $qb = $this->createQueryBuilder('f')
            ->leftJoin(Parcours::class, 'p', 'WITH', 'f.parcours = p.id')
            ->join(Formation::class, 'fo', 'WITH', 'p.formation = fo.id')
            ->join(DpeParcours::class, 'dp', 'WITH', 'p.id = dp.parcours')
            ->leftJoin(User::class, 'u', 'WITH', 'f.responsableFicheMatiere = u.id')
            ->andWhere('dp.campagneCollecte = :campagneCollecte')
            ->andWhere('f.horsDiplome = 0')
            ->orWhere('f.horsDiplome IS NULL')
            ->setParameter('campagneCollecte', $campagneCollecte);

        $this->addFiltres($qb, $options);

        return $qb->getQuery()->getResult();
    }

    public function countByAdmin(CampagneCollecte $campagneCollecte, array $options): ?int
    {
        $qb = $this->createQueryBuilder('f')
            ->leftJoin(Parcours::class, 'p', 'WITH', 'f.parcours = p.id')
            ->join(Formation::class, 'fo', 'WITH', 'p.formation = fo.id')
            ->join(DpeParcours::class, 'dp', 'WITH', 'p.id = dp.parcours')
            ->leftJoin(User::class, 'u', 'WITH', 'f.responsableFicheMatiere = u.id')
            ->andWhere('dp.campagneCollecte = :campagneCollecte')
            ->andWhere('f.horsDiplome = 0')
            ->orWhere('f.horsDiplome IS NULL')
            ->setParameter('campagneCollecte', $campagneCollecte);

        $this->addFiltres($qb, $options, true);

        return $qb->select('count(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

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

    public function findByHd(CampagneCollecte $campagneCollecte, array $options): array
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

    public function countByHd(CampagneCollecte $campagneCollecte, array $options): ?int
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

    public function findByResponsable(?UserInterface $user, CampagneCollecte $campagneCollecte, array $options): array
    {
        $query = $this->createQueryBuilder('f')
            ->leftJoin('f.parcours', 'p')
            ->join('p.formation', 'fo')
            ->join('p.dpeParcours', 'dp')
            ->orWhere('(fo.responsableMention = :parcours OR fo.coResponsable = :parcours)')
            ->orWhere('(p.respParcours = :parcours OR p.coResponsable = :parcours)')
            ->orWhere('f.responsableFicheMatiere = :user')
            ->andWhere('dp.campagneCollecte = :campagneCollecte') // Pour la troisième requête
            ->orderBy('f.libelle', 'ASC')
            ->setParameters([
                'parcours' => $user,
                'campagneCollecte' => $campagneCollecte,
                'user' => $user
            ]);

        $this->addFiltres($query, $options);

        return $query->getQuery()
            ->getResult();
    }

    public function countByResponsable(?UserInterface $user, CampagneCollecte $campagneCollecte, array $options): ?int
    {
        $query = $this->createQueryBuilder('f')
            ->leftJoin('f.parcours', 'p')
            ->join('p.formation', 'fo')
            ->join('p.dpeParcours', 'dp')
            // Pour la première requête
            ->andWhere('(fo.responsableMention = :parcours OR fo.coResponsable = :parcours)')
            // Pour la deuxième requête
            ->orWhere('(p.respParcours = :parcours OR p.coResponsable = :parcours)')
            ->andWhere('dp.campagneCollecte = :campagneCollecte') // Pour la troisième requête
            // Ajout condition à la derniere requete
            ->andWhere('f.responsableFicheMatiere = :user')
            ->orderBy('f.libelle', 'ASC')
            ->setParameters([
                'parcours' => $user,
                'campagneCollecte' => $campagneCollecte,
                'user' => $user
            ]);

        $this->addFiltres($query, $options, true);

        return $query->select('count(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByComposanteTypeValidation(Composante $composante, CampagneCollecte $campagneCollecte, mixed $transition): array
    {
        $qb = $this->createQueryBuilder('f')
            ->join(Parcours::class, 'p', 'WITH', 'f.parcours = p.id')
            ->join(Formation::class, 'fo', 'WITH', 'p.formation = fo.id')
            ->join(DpeParcours::class, 'dp', 'WITH', 'p.id = dp.parcours')
            ->andWhere('dp.campagneCollecte = :campagneCollecte')
            ->andWhere("JSON_CONTAINS(f.etatFiche, :transition) = 1")
            ->setParameter('transition', json_encode([$transition => 1]))
            ->andWhere('fo.composantePorteuse = :composante')
            ->setParameter('campagneCollecte', $campagneCollecte)
            ->setParameter('composante', $composante);

        return $qb->getQuery()->getResult();
    }

    public function findByTypeValidation(CampagneCollecte $campagneCollecte, mixed $transition): array
    {
        $qb = $this->createQueryBuilder('f')
        ->join(Parcours::class, 'p', 'WITH', 'f.parcours = p.id')
        ->join(Formation::class, 'fo', 'WITH', 'p.formation = fo.id')
        ->join(DpeParcours::class, 'dp', 'WITH', 'p.id = dp.parcours')
        ->andWhere('dp.campagneCollecte = :campagneCollecte')
        ->andWhere("JSON_CONTAINS(f.etatFiche, :transition) = 1")
        ->setParameter('transition', json_encode([$transition => 1]))
        ->setParameter('campagneCollecte', $campagneCollecte);

        return $qb->getQuery()->getResult();
    }

    public function findByTypeValidationNull(CampagneCollecte $campagneCollecte): array
    {
        $qb = $this->createQueryBuilder('f')
            ->join(Parcours::class, 'p', 'WITH', 'f.parcours = p.id')
            ->join(Formation::class, 'fo', 'WITH', 'p.formation = fo.id')
            ->join(DpeParcours::class, 'dp', 'WITH', 'p.id = dp.parcours')
            ->andWhere('dp.campagneCollecte = :campagneCollecte')
            ->andWhere('f.etatFiche IS NULL')
            ->setParameter('campagneCollecte', $campagneCollecte);

        return $qb->getQuery()->getResult();
    }

    public function countDuplicatesCode() : array {
        return $this->createQueryBuilder('fm')
            ->select('count(fm.codeApogee)')
            ->where('fm.codeApogee IS NOT NULL')
            ->groupBy('fm.codeApogee')
            ->having('count(fm.codeApogee) > 1')
            ->getQuery()
            ->getResult();
    }

    public function findByTypeValidationHorsDiplome(CampagneCollecte $campagneCollecte, mixed $transition): array
    {
        $qb = $this->createQueryBuilder('f')
            ->where('f.horsDiplome = 1')
            //->andWhere('dp.campagneCollecte = :campagneCollecte')
            ->andWhere("JSON_CONTAINS(f.etatFiche, :transition) = 1")
            ->setParameter('transition', json_encode([$transition => 1]));
           // ->setParameter('campagneCollecte', $campagneCollecte);

        return $qb->getQuery()->getResult();
    }

    public function findForParcoursWithKeyword(Parcours $parcours, string $keyword){
        $qb = $this->createQueryBuilder('fm');

        $qb = $qb->select('fm.id, fm.description, fm.objectifs, fm.slug, fm.libelle')
            ->where(
                $qb->expr()->like('UPPER(fm.description)', 'UPPER(:keyword)')
            )
            ->join('fm.parcours', 'p', 'WITH', 'fm.parcours = :parcours')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->setParameter('parcours', $parcours);

        return $qb->getQuery()->getResult();
    }

    public function findCountForKeyword(string $keyword) : array {
        $qb = $this->createQueryBuilder('fm');

        $qb = $qb->select('COUNT(fm.id) AS nombre_total')
            ->join('fm.parcours', 'p', 'WITH', 'fm.parcours = p.id')
            ->join('p.formation', 'f', 'WITH', 'p.formation = f.id')
            ->join('f.mention', 'm')
            ->join('f.typeDiplome', 'td')
            ->where(
                $qb->expr()->like('UPPER(fm.description)', 'UPPER(:keyword)')
            )
            ->orWhere(
                $qb->expr()->like('UPPER(fm.objectifs)', 'UPPER(:keyword)')
            )
            ->setParameter('keyword', '%' . $keyword . '%')
        ->getQuery()
        ->getResult();

        return $qb;

    }

    public function findFicheMatiereWithKeywordAndPagination(string $keyword, int $pageNumber, bool $paginate = true) : array {
        $qb = $this->createQueryBuilder('fm');

        $firstResults = $pageNumber > 1 ? ($pageNumber - 1) * 30 : 0;

        $qb = $qb->select(
            [
                'fm.id AS fiche_matiere_id', 'fm.slug AS fiche_matiere_slug',
                'fm.objectifs AS fiche_matiere_objectifs', 'fm.description AS fiche_matiere_description',
                'fm.libelle AS fiche_matiere_libelle', 'p.id AS parcours_id',
                'fm.sigle AS formation_sigle', 'm.libelle AS mention_libelle',
                'p.libelle AS parcours_libelle', 'td.libelle AS type_diplome_libelle',
                'p.sigle AS parcours_sigle'
            ]
        )
        ->join('fm.parcours', 'p', 'WITH', 'fm.parcours = p.id')
        ->join('p.formation', 'f', 'WITH', 'p.formation = f.id')
        ->join('f.mention', 'm')
        ->join('f.typeDiplome', 'td')
        ->where(
            $qb->expr()->like('UPPER(fm.description)', 'UPPER(:keyword)')
        )
        ->orWhere(
            $qb->expr()->like('UPPER(fm.objectifs)', 'UPPER(:keyword)')
        )
        ->setParameter('keyword', '%' . $keyword . '%');
        if($paginate){
            $qb = $qb->setFirstResult($firstResults)
                ->setMaxResults(30);
        }
        $qb = $qb->getQuery()
            ->getResult();

        return $qb;
    }
}
