<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/FormationStructure.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:10
 */

namespace App\Classes;

use App\Entity\Formation;
use App\Entity\Parcours;
use App\TypeDiplome\TypeDiplomeRegistry;

class FormationStructure
{
    public function __construct(
        private readonly TypeDiplomeRegistry $typeDiplomeRegistry
    ) {
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    public function genereStructre(Parcours $parcours): void
    {
        $formation = $parcours->getFormation();
        if ($formation === null) {
            throw new \RuntimeException('La formation n\'est pas définie');
        }

        $typeDiplome = $this->typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome());
        $typeDiplome->genereStructure($formation, $parcours);
    }

    public function genereStructrePasParcours(Formation $formation): void
    {
        $typeDiplome = $this->typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome());
        $typeDiplome->genereStructure($formation, $formation->getParcours()->first());
    }
}
