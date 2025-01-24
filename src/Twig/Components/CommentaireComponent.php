<?php

namespace App\Twig\Components;

use Doctrine\Common\Collections\Collection;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('commentaire')]
final class CommentaireComponent
{
    use DefaultActionTrait;

    public Collection $commentaires;
    public int $id;
    public string $type;
    public string $zone;

    public function hasComments(): bool
    {
        foreach ($this->commentaires as $commentaire) {
            if ($commentaire->getZone() === $this->zone) {
                return true;
            }
        }

        return false;
    }

}
