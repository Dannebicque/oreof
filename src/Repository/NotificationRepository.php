<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Repository/NotificationRepository.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 05/02/2023 17:48
 */

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\Parcours;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 *
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function save(Notification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Notification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Notification[]
     */
    public function findMutualisationPendingForUser(User $user, ?Parcours $parcours = null): array
    {
        $notifications = $this->findBy([
            'destinataire' => $user,
            'isRead' => false,
        ], [
            'created' => 'DESC',
        ]);

        return array_values(array_filter(
            $notifications,
            static fn(Notification $notification): bool => $notification->isPendingForParcours($parcours?->getId())
        ));
    }

    public function countMutualisationPendingForUser(User $user, ?Parcours $parcours = null): int
    {
        return count($this->findMutualisationPendingForUser($user, $parcours));
    }
}
