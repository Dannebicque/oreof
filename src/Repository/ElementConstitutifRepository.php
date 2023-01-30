<?php

namespace App\Repository;

use App\Entity\Composante;
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

    public function findByRoleUser(?UserInterface $user)
    {
        $roles = $user->getRoles();

        if (in_array('ROLE_SES', $roles) || in_array('ROLE_ADMIN', $roles)) {
            //tous les droits on affiche tout (trier?)
            return $this->findAll();
        }

        if (in_array('ROLE_RESP_DPE', $roles)) {
            //filtrer pour les formations de la composante
            $filtre1 = $this->createQueryBuilder('ec')
                ->innerJoin(Ue::class, 'ue', 'WITH', 'ue.id = ec.ue')
                ->innerJoin(Semestre::class, 's', 'WITH', 's.id = ue.semestre')
                // ->leftJoin(Parcours::class, 'p', 'WITH', 'p.id = s.parcours')
                ->innerJoin(Formation::class, 'f', 'WITH', 'f.id = s.formation')
                ->innerJoin(Composante::class, 'c', 'WITH', 'c.id = f.composante')
                ->where('c.responsableDpe = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult();

            $filtre2 = $this->createQueryBuilder('ec')
                ->innerJoin(Ue::class, 'ue', 'WITH', 'ue.id = ec.ue')
                ->innerJoin(Semestre::class, 's', 'WITH', 's.id = ue.semestre')
                ->leftJoin(Parcours::class, 'p', 'WITH', 'p.id = s.parcours')
                ->innerJoin(Formation::class, 'f', 'WITH', 'f.id = p.formation')
                ->innerJoin(Composante::class, 'c', 'WITH', 'c.id = f.composante')
                ->where('c.responsableDpe = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult();

            return array_merge($filtre1, $filtre2);

        }

        if (in_array('ROLE_RESP_FORMATION', $roles)) {
            $filtre1 = $this->createQueryBuilder('ec')
                ->innerJoin(Ue::class, 'ue', 'WITH', 'ue.id = ec.ue')
                ->innerJoin(Semestre::class, 's', 'WITH', 's.id = ue.semestre')
                // ->leftJoin(Parcours::class, 'p', 'WITH', 'p.id = s.parcours')
                ->innerJoin(Formation::class, 'f', 'WITH', 'f.id = s.formation')
                ->where('f.responsableFormation = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult();

            $filtre2 = $this->createQueryBuilder('ec')
                ->innerJoin(Ue::class, 'ue', 'WITH', 'ue.id = ec.ue')
                ->innerJoin(Semestre::class, 's', 'WITH', 's.id = ue.semestre')
                ->leftJoin(Parcours::class, 'p', 'WITH', 'p.id = s.parcours')
                ->innerJoin(Formation::class, 'f', 'WITH', 'f.id = p.formation')
                ->where('f.responsableFormation = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult();

            return array_merge($filtre1, $filtre2);
        }

        if (in_array('ROLE_RESP_EC', $roles)) {
            //todo: juste les EC qui me concerne ?
            return $this->findBy(['responsableEc' => $user]);
        }

        return []; //todo: ? est-ce vrai ?
    }
}
