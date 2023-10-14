<?php

namespace App\Entity;

use App\Repository\CommentaireParcoursRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentaireParcoursRepository::class)]
class CommentaireParcours extends Commentaire
{

    #[ORM\ManyToOne(inversedBy: 'commentaires')]
    private ?Parcours $parcours = null;


    public function getParcours(): ?Parcours
    {
        return $this->parcours;
    }

    public function setParcours(?Parcours $parcours): static
    {
        $this->parcours = $parcours;

        return $this;
    }
}
