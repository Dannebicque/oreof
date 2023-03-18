<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/McccRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 20/02/2023 15:58
 */

namespace App\Repository;

use App\Entity\Mccc;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mccc>
 *
 * @method Mccc|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mccc|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mccc[]    findAll()
 * @method Mccc[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class McccRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mccc::class);
    }

    public function save(Mccc $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Mccc $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
