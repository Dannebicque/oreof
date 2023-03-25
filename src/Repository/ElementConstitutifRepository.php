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
use App\Entity\Composante;
use App\Entity\EcUe;
use App\Entity\ElementConstitutif;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\Ue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<ElementConstitutif>
 *
 * @method ElementConstitutif|null find($id, $lockMode = null, $lockVersion = null)
 * @method ElementConstitutif|null findOneBy(array $criteria, array $orderBy = null)
 * @method ElementConstitutif[]    findAll()
 * @method ElementConstitutif[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ElementConstitutifRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ElementConstitutif::class);
    }

    public function save(ElementConstitutif $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ElementConstitutif $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByComposanteDpe(UserInterface $user, AnneeUniversitaire $anneeUniversitaire): array
    {
        return $this->createQueryBuilder('ec')
            ->join('ec.ecUes', 'ecue')
            ->join('ecue.ue', 'ue')
            ->innerJoin(Semestre::class, 's', 'WITH', 's.id = ue.semestre')
            ->join('s.semestreParcours', 'sp')
            ->innerJoin(Parcours::class, 'p', 'WITH', 'p.id = sp.parcours')
            ->innerJoin(Formation::class, 'f', 'WITH', 'f.id = p.formation')
            ->innerJoin(Composante::class, 'c', 'WITH', 'f.composantePorteuse = c.id')
            ->where('c.responsableDpe = :user')
            ->andWhere('f.anneeUniversitaire = :anneeUniversitaire')
            ->setParameter('user', $user)
            ->setParameter('anneeUniversitaire', $anneeUniversitaire)
            ->getQuery()
            ->getResult();
    }

    public function findByResponsableFormation(UserInterface $user, AnneeUniversitaire $anneeUniversitaire): array
    {
        return $this->createQueryBuilder('ec')
            ->join('ec.ecUes', 'ecue')
            ->join('ecue.ue', 'ue')
            ->innerJoin(Semestre::class, 's', 'WITH', 's.id = ue.semestre')
            ->join('s.semestreParcours', 'sp')
            ->innerJoin(Parcours::class, 'p', 'WITH', 'p.id = sp.parcours')
            ->innerJoin(Formation::class, 'f', 'WITH', 'f.id = p.formation')
            ->where('f.responsableMention = :user')
            ->andWhere('f.anneeUniversitaire = :anneeUniversitaire')
            ->setParameter('user', $user)
            ->setParameter('anneeUniversitaire', $anneeUniversitaire)
            ->getQuery()
            ->getResult();
    }

    public function findByResponsableEc(UserInterface $user, AnneeUniversitaire $anneeUniversitaire): array
    {
        return $this->createQueryBuilder('ec')
            ->join('ec.ecUes', 'ecue')
            ->join('ecue.ue', 'ue')
            ->innerJoin(Semestre::class, 's', 'WITH', 's.id = ue.semestre')
            ->join('s.semestreParcours', 'sp')
            ->innerJoin(Parcours::class, 'p', 'WITH', 'p.id = sp.parcours')
            ->innerJoin(Formation::class, 'f', 'WITH', 'f.id = p.formation')
            ->where('ec.responsableEc = :user')
            ->andWhere('f.anneeUniversitaire = :anneeUniversitaire')
            ->setParameter('user', $user)
            ->setParameter('anneeUniversitaire', $anneeUniversitaire)
            ->getQuery()
            ->getResult();
    }

    public function findByAllAnneUniversitaire(AnneeUniversitaire $anneeUniversitaire): array
    {
        return $this->createQueryBuilder('ec')
            ->join('ec.ecUes', 'ecue')
            ->join('ecue.ue', 'ue')
            ->innerJoin(Semestre::class, 's', 'WITH', 's.id = ue.semestre')
            ->join('s.semestreParcours', 'sp')
            ->innerJoin(Parcours::class, 'p', 'WITH', 'p.id = sp.parcours')
            ->innerJoin(Formation::class, 'f', 'WITH', 'f.id = p.formation')
            ->andWhere('f.anneeUniversitaire = :anneeUniversitaire')
            ->setParameter('anneeUniversitaire', $anneeUniversitaire)
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    public function findLastEc(Ue $ue): int
    {
        return $this->createQueryBuilder('ec')
            ->select('MAX(ec.ordre)')
            ->andWhere('ec.ue = :ue')
            ->setParameter('ue', $ue)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }

    public function findByUeOrdre(?int $ordreDestination, ?Ue $ue): ?array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.ue = :ue')
            ->andWhere('e.ordre = :ordre')
            ->setParameter('ue', $ue)
            ->setParameter('ordre', $ordreDestination)
            ->getQuery()
            ->getResult();
    }

    public function findByUeSubOrdre(?int $ordreDestination, ?Ue $ue, int $ordreEc): ?ElementConstitutif
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.ue = :ue')
            ->andWhere('e.ordre = :ordre')
            ->andWhere('e.subOrdre = :subOrdre')
            ->setParameter('ue', $ue)
            ->setParameter('ordre', $ordreEc)
            ->setParameter('subOrdre', $ordreDestination)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLastEcSubOrdre(?Ue $ue, ?int $ordre): int
    {
        return $this->createQueryBuilder('ec')
            ->select('MAX(ec.subOrdre)')
            ->andWhere('ec.ordre = :ordre')
            ->andWhere('ec.ue = :ue')
            ->setParameter('ue', $ue)
            ->setParameter('ordre', $ordre)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }
}
