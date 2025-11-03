<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/NotificationListeRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 26/01/2023 20:42
 */

namespace App\Repository;

use App\Entity\NotificationListe;
use App\Entity\Profil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NotificationListe>
 *
 * @method NotificationListe|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotificationListe|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationListe[]    findAll()
 * @method NotificationListe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationListeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationListe::class);
    }

    public function save(NotificationListe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(NotificationListe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findIfExist(Profil $profil, string $workflow, string $step): ?NotificationListe
    {
        return $this->createQueryBuilder('nl')
            ->where('nl.profil = :profil')
            ->andWhere('nl.workflow = :workflow')
            ->andWhere('nl.step = :step')
            ->setParameter('profil', $profil)
            ->setParameter('workflow', $workflow)
            ->setParameter('step', $step)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
