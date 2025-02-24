<?php

namespace App\Entity;

use App\Repository\CommentaireFormationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentaireFormationRepository::class)]
class CommentaireFormation extends Commentaire
{

    #[ORM\ManyToOne(inversedBy: 'commentaires')]
    private ?Formation $formation = null;



    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    public function setFormation(?Formation $formation): static
    {
        $this->formation = $formation;

        return $this;
    }
}
