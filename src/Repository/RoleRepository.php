<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/RoleRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/02/2023 09:30
 */

namespace App\Repository;

use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Role>
 *
 * @method Role|null find($id, $lockMode = null, $lockVersion = null)
 * @method Role|null findOneBy(array $criteria, array $orderBy = null)
 * @method Role[]    findAll()
 * @method Role[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    public function save(Role $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Role $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByPermission(string $attribute): array
    {
        $t = [];
        $all = $this->findAll();
        foreach ($all as $role) {
            if (in_array($attribute, $role->getDroits(), true)) {
                $t[] = $role->getCodeRole();
            }
        }

        return array_unique($t);
    }

    public function findByAll(): array
    {
        return $this->findBy(['porte' => 'All'], ['libelle' => 'ASC']);
    }

    public function findByDpe(): array
    {
        return $this->findBy(['onlyAdmin' => false], ['libelle' => 'ASC']);
    }

    public function findByCentre(string $centre): array
    {
        return $this->findBy(['centre' => $centre], ['libelle' => 'ASC']);
    }

    public function findByCentreDpe(string $centre, bool $dpe): array
    {
        return $this->findBy(['centre' => $centre, 'onlyAdmin' => $dpe], ['libelle' => 'ASC']);
    }
}
