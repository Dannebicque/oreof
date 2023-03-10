<?php

namespace App\Repository;

use App\Entity\Composante;
use App\Entity\User;
use App\Entity\UserCentre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Composante>
 *
 * @method Composante|null find($id, $lockMode = null, $lockVersion = null)
 * @method Composante|null findOneBy(array $criteria, array $orderBy = null)
 * @method Composante[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ComposanteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Composante::class);
    }

    public function findAll(): array
    {
        return $this->findBy([], ['libelle' => 'ASC']);
    }

    public function save(Composante $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Composante $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByCentreGestion(UserInterface $user): array
    {
        $qb = $this->createQueryBuilder('c');
        $qb->innerJoin(UserCentre::class, 'cg', 'WITH', 'c.id = cg.composante');
        $qb->where('cg.user = :user');
        $qb->setParameter('user', $user->getId());

        return $qb->getQuery()->getResult();
    }
}
