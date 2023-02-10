<?php

namespace App\Classes;

use App\Entity\Formation;
use App\TypeDiplome\TypeDiplomeRegistry;

class FormationStructure
{
    public function __construct(
        private readonly TypeDiplomeRegistry $typeDiplomeRegistry
    ){}

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    public function genereStructre(Formation $formation): void
    {
        $typeDiplome = $this->typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome());
        $typeDiplome->genereStructure($formation);
    }
}
