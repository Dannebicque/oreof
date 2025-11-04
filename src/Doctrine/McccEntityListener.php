<?php
// src/Doctrine/McccLineEntityListener.php
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

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Mccc::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Mccc::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: Mccc::class)]
final class McccEntityListener
{
    public function __construct(private McccCompletionChecker $checker)
    {
    }

    public function postPersist(Mccc $mccc, PostPersistEventArgs $args): void
    {
        if ($owner = $mccc->getOwner()) {
            $this->updateOwnerFlag($owner, $args);
        }
    }

    private function updateOwnerFlag(FicheMatiere|ElementConstitutif $owner, object $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        $owner->setEtatMccc($this->checker->isCompletedForOwner($owner));

        // Recompute le change set pour forcer le flush du flag
        $meta = $em->getClassMetadata($owner::class);
        $uow->recomputeSingleEntityChangeSet($meta, $owner);
    }

    public function preUpdate(Mccc $mccc, PreUpdateEventArgs $args): void
    {
        $ownersToUpdate = [];

        // Déplacement FM/EC ?
        if ($args->hasChangedField('ficheMatiere')) {
            [$old, $new] = [$args->getOldValue('ficheMatiere'), $args->getNewValue('ficheMatiere')];
            if ($old instanceof FicheMatiere) $ownersToUpdate[] = $old;
            if ($new instanceof FicheMatiere) $ownersToUpdate[] = $new;
        }
        if ($args->hasChangedField('elementConstitutif')) {
            [$old, $new] = [$args->getOldValue('elementConstitutif'), $args->getNewValue('elementConstitutif')];
            if ($old instanceof ElementConstitutif) $ownersToUpdate[] = $old;
            if ($new instanceof ElementConstitutif) $ownersToUpdate[] = $new;
        }

        // Changement métier impactant la complétion
        if ($args->hasChangedField('weight') || $args->hasChangedField('type')) {
            if ($owner = $mccc->getOwner()) $ownersToUpdate[] = $owner;
        }

        // Déduplique puis met à jour
        $ownersToUpdate = array_values(array_unique($ownersToUpdate, SORT_REGULAR));
        foreach ($ownersToUpdate as $owner) {
            $this->updateOwnerFlag($owner, $args);
        }
    }

    public function postRemove(Mccc $mccc, PostRemoveEventArgs $args): void
    {
        if ($owner = $mccc->getOwner()) {
            $this->updateOwnerFlag($owner, $args);
        }
    }
}
