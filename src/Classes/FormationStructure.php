<?php

namespace App\Classes;

use App\Entity\Formation;
use App\Entity\Parcours;
use App\TypeDiplome\TypeDiplomeRegistry;

class FormationStructure
{
    public function __construct(
        private readonly TypeDiplomeRegistry $typeDiplomeRegistry
    ){}

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    public function genereStructre(Parcours $parcours): void
    {
        $formation = $parcours->getFormation();
        if ($formation === null) {
            throw new \Exception('La formation n\'est pas définie');
        }

        $typeDiplome = $this->typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome());
        $typeDiplome->genereStructure($formation, $parcours);
    }

    public function genereStructrePasParcours(Formation $formation): void
    {
        if ($formation === null) {
            throw new \Exception('La formation n\'est pas définie');
        }

        $typeDiplome = $this->typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome());
        $typeDiplome->genereStructure($formation, $formation->getParcours()->first());
    }
}
