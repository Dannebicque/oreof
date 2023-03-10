<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use function get_class;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findAll(): array
    {
        return $this->findBy([], ['nom' => 'ASC', 'prenom' => 'ASC']);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }

    public function findNotEnableAvecDemande(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.isEnable = :isEnable')
            ->andWhere('u.dateDemande IS NOT NULL')
            ->andWhere('u.isValideAdministration = :isValideAdministration')
            ->setParameter('isEnable', false)
            ->setParameter('isValideAdministration', false)
            ->getQuery()
            ->getResult();
    }

    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%"' . $role . '"%')
            ->getQuery()
            ->getResult();
    }

    public function findEnable(
        float|bool|int|string|null $sort,
        string|null $direction
    ): array {
        return $this->createQueryBuilder('u')
            ->where('u.isEnable = :isEnable')
            ->setParameter('isEnable', true)
            ->addOrderBy('u.' . $sort, $direction)
            ->getQuery()
            ->getResult();
    }

    public function findEnableBySearch(
        string|null $q,
        float|bool|int|string|null $sort,
        string|null $direction
    ) {
        return $this->createQueryBuilder('u')
            ->where('u.isEnable = :isEnable')
            ->andWhere('u.nom LIKE :q OR u.prenom LIKE :q OR u.email LIKE :q OR u.username LIKE :q')
            ->setParameter('isEnable', true)
            ->setParameter('q', '%' . $q . '%')
            ->addOrderBy('u.' . $sort, $direction)
            ->getQuery()
            ->getResult();
    }
}
