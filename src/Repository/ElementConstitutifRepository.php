<?php

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

//    public function findByRoleUser(UserInterface $user): array
//    {
//        $roles = $user->getRoles();
//
//        if (in_array('ROLE_SES', $roles) || in_array('ROLE_ADMIN', $roles)) {
//            //tous les droits on affiche tout (trier?)
//            return $this->findAll();
//        }
//
//        if (in_array('ROLE_RESP_DPE', $roles)) {
//            //filtrer pour les formations de la composante
//            return $this->findByComposanteDpe($user);
//
//        }
//
//        if (in_array('ROLE_RESP_FORMATION', $roles)) {
//            return $this->findByResponsableFormation($user);
//        }
//
//        if (in_array('ROLE_RESP_EC', $roles)) {
//            //todo: juste les EC qui me concerne ?
//            return $this->findBy(['responsableEc' => $user]);
//        }
//
//        return []; //todo: ? est-ce vrai ?
//    }

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
}
