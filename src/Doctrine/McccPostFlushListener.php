<?php
// src/Doctrine/McccPostFlushListener.php
namespace App\Doctrine;

use App\Service\McccCompletionChecker;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PostFlushEventArgs;

#[AsDoctrineListener(event: Events::postFlush)]
final class McccPostFlushListener
{
    private bool $isFlushing = false;

    public function __construct(
        private readonly McccEntityListener    $mcccEntityListener,
        private readonly McccCompletionChecker $checker,
    )
    {
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if ($this->isFlushing || empty($this->mcccEntityListener->getOwnersToFlush())) {
            return;
        }

        $em = $args->getObjectManager();
        $this->isFlushing = true;

        foreach ($this->mcccEntityListener->getOwnersToFlush() as $owner) {
            $owner->setEtatMccc(
                $this->checker->isCompletedForOwner($owner) ? 'Complet' : 'A saisir'
            );
            $em->persist($owner);
        }

        $this->mcccEntityListener->clearOwnersToFlush();
        $em->flush();

        $this->isFlushing = false;
    }
}
