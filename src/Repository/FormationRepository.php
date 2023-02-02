<?php

namespace App\Repository;

use App\Entity\Composante;
use App\Entity\Formation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Formation>
 *
 * @method Formation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Formation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Formation[]    findAll()
 * @method Formation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Formation::class);
    }

    public function save(Formation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Formation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByRoleUser(UserInterface $user): array
    {
        $roles = $user->getRoles();

        if (in_array('ROLE_SES', $roles) || in_array('ROLE_ADMIN', $roles)) {
            return $this->findAll();
        }

        if (in_array('ROLE_RESP_DPE', $roles)) {
            //todo: formation de la composante ? Le Resp DPE est dans la composante ??
            return $this->findByComposateDpe($user);
        }

        if (in_array('ROLE_RESP_FORMATION', $roles)) {
            //todo: formation de la composante ? Le Resp DPE est dans la composante ??
            return $this->findBy(['responsableMention' => $user]);
        }

        if (in_array('ROLE_RESP_EC', $roles)) {
            //todo: les formations dans lesquels il est impliqué ?
            //todo: comment on a le lien DPE => Composante et Composante => Formation ?
        }

        return [];
    }

    private function findByComposateDpe(UserInterface $user): array
    {
        return $this->createQueryBuilder('f')
            ->innerJoin(Composante::class, 'c', 'WITH', 'f.composantePorteuse = c.id')
            ->where('c.responsableDpe = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
