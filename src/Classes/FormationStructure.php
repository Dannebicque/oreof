<?php

namespace App\Classes;

use App\Entity\Formation;
use App\TypeDiplome\TypeDiplomeRegistry;
use Doctrine\ORM\EntityManagerInterface;

class FormationStructure
{
    public function __construct(
        private TypeDiplomeRegistry $typeDiplomeRegistry,
        private EntityManagerInterface $entityManager
    ){}

    public function genereStructre(Formation $formation): void
    {
        $typeDiplome = $this->typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome());
        $typeDiplome->genereStructure($formation);
    }
}
