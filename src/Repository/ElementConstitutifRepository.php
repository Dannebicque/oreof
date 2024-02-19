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
use App\Entity\ElementConstitutif;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\SemestreParcours;
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

    public function findByComposanteDpe(UserInterface $user, CampagneCollecte $campagneCollecte): array
    {
        return $this->createQueryBuilder('ec')
            ->join('ec.ecUes', 'ecue')
            ->join('ecue.ue', 'ue')
            ->innerJoin(Semestre::class, 's', 'WITH', 's.id = ue.semestre')
            ->join('s.semestreParcours', 'sp')
            ->innerJoin(Parcours::class, 'p', 'WITH', 'p.id = sp.parcours')
            ->innerJoin(Formation::class, 'f', 'WITH', 'f.id = p.formation')
            ->join('f.dpeParcours', 'dp')
            ->innerJoin(Composante::class, 'c', 'WITH', 'f.composantePorteuse = c.id')
            ->where('c.responsableDpe = :user')
            ->andWhere('dp.campagneCollecte = :campagneCollecte')
            ->setParameter('user', $user)
            ->setParameter('campagneCollecte', $campagneCollecte)
            ->getQuery()
            ->getResult();
    }

    public function findByResponsableFormation(UserInterface $user, CampagneCollecte $campagneCollecte): array
    {
        return $this->createQueryBuilder('ec')
            ->join('ec.ecUes', 'ecue')
            ->join('ecue.ue', 'ue')
            ->innerJoin(Semestre::class, 's', 'WITH', 's.id = ue.semestre')
            ->join('s.semestreParcours', 'sp')
            ->innerJoin(Parcours::class, 'p', 'WITH', 'p.id = sp.parcours')
            ->join('p.dpeParcourss', 'dp')
            ->innerJoin(Formation::class, 'f', 'WITH', 'f.id = p.formation')
            ->where('f.responsableMention = :user')
            ->andWhere('dp.campagneCollecte = :campagneCollecte')
            ->setParameter('user', $user)
            ->setParameter('campagneCollecte', $campagneCollecte)
            ->getQuery()
            ->getResult();
    }

    public function findByResponsableEc(UserInterface $user, CampagneCollecte $campagneCollecte): array
    {
        return $this->createQueryBuilder('ec')
            ->join('ec.ecUes', 'ecue')
            ->join('ecue.ue', 'ue')
            ->innerJoin(Semestre::class, 's', 'WITH', 's.id = ue.semestre')
            ->join('s.semestreParcours', 'sp')
            ->innerJoin(Parcours::class, 'p', 'WITH', 'p.id = sp.parcours')
            ->join('p.dpeParcourss', 'dp')
            ->innerJoin(Formation::class, 'f', 'WITH', 'f.id = p.formation')
            ->where('ec.responsableEc = :user')
            ->andWhere('dp.campagneCollecte = :campagneCollecte')
            ->setParameter('user', $user)
            ->setParameter('campagneCollecte', $campagneCollecte)
            ->getQuery()
            ->getResult();
    }

    public function findByAllDpe(CampagneCollecte $campagneCollecte): array
    {
        return $this->createQueryBuilder('ec')
            ->join('ec.ecUes', 'ecue')
            ->join('ecue.ue', 'ue')
            ->innerJoin(Semestre::class, 's', 'WITH', 's.id = ue.semestre')
            ->join('s.semestreParcours', 'sp')
            ->innerJoin(Parcours::class, 'p', 'WITH', 'p.id = sp.parcours')
            ->join('p.dpeParcourss', 'dp')
            ->innerJoin(Formation::class, 'f', 'WITH', 'f.id = p.formation')
            ->andWhere('f.campagneCollecte = :campagneCollecte')
            ->setParameter('campagneCollecte', $campagneCollecte)
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
            ->andWhere('e.ecParent IS NULL')
            ->andWhere('e.ordre = :ordre')
            ->setParameter('ue', $ue)
            ->setParameter('ordre', $ordreDestination)
            ->getQuery()
            ->getResult();
    }

    public function findByUeSubOrdre(?int $ordreDestination, ElementConstitutif $elementConstitutif): ?array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.ecParent = :ec')
            ->andWhere('e.ordre = :ordre')
            ->setParameter('ec', $elementConstitutif)
            ->setParameter('ordre', $ordreDestination)
            ->getQuery()
            ->getResult();
    }

    public function findLastEcEnfant(ElementConstitutif $elementConstitutif): int
    {
        return $this->createQueryBuilder('e')
            ->select('MAX(e.ordre)')
            ->andWhere('e.ecParent = :ecParent')
            ->setParameter('ecParent', $elementConstitutif)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }

    public function findByParcours(Parcours $parcours): array
    {
        return $this->createQueryBuilder('ec')
            ->join('ec.ue', 'ue')
            ->join('ec.parcours', 'p')
            ->innerJoin(Semestre::class, 's', 'WITH', 's.id = ue.semestre')
            ->join('p.semestreParcours', 'sp')
            ->where('ec.parcours = :parcours')
            ->setParameter('parcours', $parcours)
            ->addOrderBy('sp.ordre', 'ASC')
            ->addOrderBy('ue.ordre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByFormation(Formation $formation)
    {
        return $this->createQueryBuilder('ec')
            ->join('ec.parcours', 'p')
            ->where('p.formation = :formation')
            ->setParameter('formation', $formation)
            ->getQuery()
            ->getResult();
    }

    public function findWithAc()
    {
        return $this->createQueryBuilder('ec')
            ->join('ec.apprentissagesCritiques', 'ac')
            ->getQuery()
            ->getResult();
    }

    public function getByUe(?Ue $ue): array
    {
        return $this->createQueryBuilder('ec')
            ->leftJoin('ec.ficheMatiere', 'fm')
            ->leftJoin('ec.typeEc', 'te')
            ->addSelect('fm')
            ->addSelect('te')
            ->andWhere('ec.ue = :ue')
            ->setParameter('ue', $ue)
            ->orderBy('ec.ordre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findWithParcours(): array
    {
        return $this->createQueryBuilder('ec')
            ->join('ec.ue', 'ue')
            ->join('ue.semestre', 's')
            ->innerJoin(SemestreParcours::class, 'sp', 'WITH', 's.id = sp.semestre')
            ->join('sp.parcours', 'p')
            ->join('p.formation', 'f')
//            //->addSelect('ue', 's', 'sp', 'p', 'f')
            ->andWhere('ec.parcours IS NOT NULL')
            ->getQuery()
            ->getResult();
    }
}
