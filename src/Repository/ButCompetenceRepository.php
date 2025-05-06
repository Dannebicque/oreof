<?php

namespace App\Repository;

use App\Entity\ButCompetence;
use App\Entity\CampagneCollecte;
use App\Entity\Formation;
use App\Entity\Ue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ButCompetence>
 *
 * @method ButCompetence|null find($id, $lockMode = null, $lockVersion = null)
 * @method ButCompetence|null findOneBy(array $criteria, array $orderBy = null)
 * @method ButCompetence[]    findAll()
 * @method ButCompetence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ButCompetenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ButCompetence::class);
    }

    public function save(ButCompetence $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ButCompetence $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByUe(Ue $ue, Formation $formation, CampagneCollecte $campagneCollecte): ?ButCompetence
    {
        $query = $this->createQueryBuilder('b')
            ->where('b.numero = :ue')
            ->andWhere('b.formation = :formation')
            ->andWhere('b.campagneCollecte = :campagneCollecte')
            ->setParameter('ue', $ue->getOrdre())
            ->setParameter('formation', $formation)
            ->setParameter('campagneCollecte', $campagneCollecte)
            ->getQuery()
            ->getResult();

        if (count($query) === 1) {
            return $query[0];
        }

        // on essaye de trouver une compétence avec le même libellé court que l'UE
        // pas forcément idéal, car dépendant de la syntaxe des deux informations... A améliorer
        foreach ($query as $butCompetence) {
            if ($butCompetence->getNomCourt() === $ue->getLibelle()) {
                return $butCompetence;
            }
        }
    }
}
