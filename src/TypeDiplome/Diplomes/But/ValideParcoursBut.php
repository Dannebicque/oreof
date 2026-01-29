<?php
/*
 * Copyright (c) 2025. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/But/ValideParcoursBut.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 28/05/2025 15:30
 */

namespace App\TypeDiplome\Diplomes\But;

use App\DTO\StructureParcours;
use App\DTO\StructureSemestre;
use App\Entity\Semestre;
use App\Entity\Ue;
use App\Service\Validation\Dto\ValidationResult;
use App\TypeDiplome\ValideParcoursInterface;

class ValideParcoursBut implements ValideParcoursInterface
{

    public function valideSemestre(StructureSemestre $structureSemestre): ValidationResult
    {
        // TODO: Implement valideSemestre() method.
    }

    public function valideUe(Ue $ue): ValidationResult
    {
        // TODO: Implement valideUe() method.
    }

    public function valideParcours(StructureParcours $structureParcours): ValidationResult
    {
        // TODO: Implement valideParcours() method.
    }
}
