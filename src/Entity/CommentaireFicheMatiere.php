<?php

namespace App\Entity;

use App\Repository\CommentaireFicheMatiereRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentaireFicheMatiereRepository::class)]
class CommentaireFicheMatiere extends Commentaire
{

    #[ORM\ManyToOne(inversedBy: 'commentaires')]
    private ?FicheMatiere $ficheMatiere = null;

    public function getFicheMatiere(): ?FicheMatiere
    {
        return $this->ficheMatiere;
    }

    public function setFicheMatiere(?FicheMatiere $ficheMatiere): static
    {
        $this->ficheMatiere = $ficheMatiere;

        return $this;
    }
}
