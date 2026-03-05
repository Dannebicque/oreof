<?php


// src/Doctrine/McccEntityListener.php
namespace App\Doctrine;

use App\Service\McccCompletionChecker;
use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\Mccc;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Mccc::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Mccc::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: Mccc::class)]
final class McccEntityListener
{
    /** @var array<FicheMatiere|ElementConstitutif> */
    private array $ownersToFlush = [];

    public function postPersist(Mccc $mccc, PostPersistEventArgs $args): void
    {
        if ($owner = $mccc->getOwner()) {
            $this->scheduleOwnerUpdate($owner);
        }
    }

    public function preUpdate(Mccc $mccc, PreUpdateEventArgs $args): void
    {
        // Déplacement FM/EC ?
        if ($args->hasChangedField('ficheMatiere')) {
            [$old, $new] = [$args->getOldValue('ficheMatiere'), $args->getNewValue('ficheMatiere')];
            if ($old instanceof FicheMatiere) {
                $this->scheduleOwnerUpdate($old);
            }
            if ($new instanceof FicheMatiere) {
                $this->scheduleOwnerUpdate($new);
            }
        }
        if ($args->hasChangedField('elementConstitutif')) {
            [$old, $new] = [$args->getOldValue('elementConstitutif'), $args->getNewValue('elementConstitutif')];
            if ($old instanceof ElementConstitutif) {
                $this->scheduleOwnerUpdate($old);
            }
            if ($new instanceof ElementConstitutif) {
                $this->scheduleOwnerUpdate($new);
            }
        }

        if ($owner = $mccc->getOwner()) {
            $this->scheduleOwnerUpdate($owner);
        }
    }

    public function postRemove(Mccc $mccc, PostRemoveEventArgs $args): void
    {
        if ($owner = $mccc->getOwner()) {
            $this->scheduleOwnerUpdate($owner);
        }
    }

    private function scheduleOwnerUpdate(FicheMatiere|ElementConstitutif $owner): void
    {
        // Déduplique par spl_object_id pour éviter les doublons d'objet
        $this->ownersToFlush[spl_object_id($owner)] = $owner;
    }

    public function getOwnersToFlush(): array
    {
        return $this->ownersToFlush;
    }

    public function clearOwnersToFlush(): void
    {
        $this->ownersToFlush = [];
    }
}
