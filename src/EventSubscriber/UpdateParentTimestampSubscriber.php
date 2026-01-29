<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/EventSubscriber/UpdateParentTimestampSubscriber.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 21/01/2026 19:18
 */

namespace App\EventSubscriber;

use App\Entity\ElementConstitutif;
use App\Entity\Mccc;
use App\Entity\Ue;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;

class UpdateParentTimestampSubscriber implements EventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [Events::onFlush];
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        $collections = [
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityUpdates(),
            $uow->getScheduledEntityDeletions(),
        ];

        $checkEntity = static function ($entity) use ($em, $uow) {
            $now = new \DateTimeImmutable();
            // adapter selon vos getters réels
            if ($entity instanceof Ue) {
                $parent = $entity->getSemestre();
            } elseif ($entity instanceof ElementConstitutif) {
                $parent = $entity->getUe()?->getSemestre();
            } elseif ($entity instanceof Mccc) {
                $parent = $entity->getEc()?->getUe()?->getSemestre();
            } else {
                $parent = null;
            }

            if ($parent !== null) {
                $parent->setLastModification($now);
                $meta = $em->getClassMetadata(get_class($parent));
                $uow->recomputeSingleEntityChangeSet($meta, $parent);
            }
        };

        // entités insérées / modifiées / supprimées
        foreach ([
                     $uow->getScheduledEntityInsertions(),
                     $uow->getScheduledEntityUpdates(),
                     $uow->getScheduledEntityDeletions(),
                 ] as $batch) {
            foreach ($batch as $entity) {
                $checkEntity($entity);
            }
        }

        // changements de collections (ajout/suppression d'éléments dans une relation)
        foreach ([
                     $uow->getScheduledCollectionUpdates(),
                     $uow->getScheduledCollectionDeletions(),
                 ] as $collections) {
            foreach ($collections as $coll) {
                $owner = $coll->getOwner();
                // si la collection appartient à une entité enfant, remonter au parent
                $checkEntity($owner);
                // éventuellement itérer sur les éléments modifiés pour trouver le parent si nécessaire
            }
        }
    }
}
