<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/UeRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 25/01/2023 20:32
 */

namespace App\Repository;

use App\Entity\Semestre;
use App\Entity\Ue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ue>
 *
 * @method Ue|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ue|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ue[]    findAll()
 * @method Ue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ue::class);
    }

    public function save(Ue $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Ue $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getMaxOrdre(Semestre $semestre): int
    {
        return $this->createQueryBuilder('u')
            ->select('MAX(u.ordre)')
            ->andWhere('u.semestre = :semestre')
            ->setParameter('semestre', $semestre)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }

    public function findByUeOrdre(?int $ordreDestination, Semestre $semestre): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.semestre = :semestre')
            ->andWhere('u.ordre = :ordre')
            ->andWhere('u.ueParent IS NULL')
            ->setParameter('semestre', $semestre)
            ->setParameter('ordre', $ordreDestination)
            ->getQuery()
            ->getResult();
    }

    public function findByUeSubOrdre(?int $ordreDestination, ?Ue $ueParent): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.ueParent = :ue')
            ->andWhere('u.ordre = :ordre')
            ->setParameter('ue', $ueParent)
            ->setParameter('ordre', $ordreDestination)
            ->getQuery()
            ->getResult();
    }

    public function findBySemestreSubOrdreAfter(?int $ordreDestination, ?Semestre $getSemestre, Ue $ueParent): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.semestre = :semestre')
            ->andWhere('u.ueParent = :ueParent')
            ->andWhere('u.ordre > :ordre')
            ->setParameter('semestre', $getSemestre)
            ->setParameter('ordre', $ordreDestination)
            ->setParameter('ueParent', $ueParent)
            ->getQuery()
            ->getResult();
    }

    public function findBySemestreOrdreAfter(?int $ordreDestination, ?Semestre $getSemestre): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.semestre = :semestre')
            ->andWhere('u.ordre > :ordre')
            ->andWhere('u.ueParent IS NULL')
            ->setParameter('semestre', $getSemestre)
            ->setParameter('ordre', $ordreDestination)
            ->getQuery()
            ->getResult();
    }

    public function getBySemestre(Semestre $semestre): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.ueRaccrochee', 'r')
            ->addSelect('r')
            ->andWhere('u.semestre = :semestre')
            ->setParameter('semestre', $semestre)
            ->getQuery()
            ->getResult();
    }

    public function countDuplicatesCode() : array {
        return $this->createQueryBuilder('ue')
            ->select('count(ue.codeApogee)')
            ->where('ue.codeApogee IS NOT NULL')
            ->groupBy('ue.codeApogee')
            ->having('count(ue.codeApogee) > 1')
            ->getQuery()
            ->getResult();
    }

    public function findFromAnneeUniversitaire(int $idCampagneCollecte) : array {
        return $this->createQueryBuilder('ue')
            ->select('ue.id')
            ->join('ue.semestre', 'semestre')
            ->join('semestre.semestreParcours', 'semP')
            ->join('semP.parcours', 'parcours')
            ->join('parcours.dpeParcours', 'dpe')
            ->join('dpe.campagneCollecte', 'campagne')
            ->andWhere('campagne.id = :idCampagne')
            ->setParameter(':idCampagne', $idCampagneCollecte)
            ->getQuery()
            ->getResult();
    }
}
