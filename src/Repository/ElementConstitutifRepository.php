<?php

namespace App\Repository;

use App\Entity\ElementConstitutif;
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
        return $this->findAll(); //todo: a faire
    }
}
