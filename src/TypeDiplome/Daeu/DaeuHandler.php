<?php
/*
 * Copyright (c) 2025. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/Daeu/DaeuHandler.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 28/05/2025 15:26
 */

namespace App\TypeDiplome\Daeu;

use App\Entity\Parcours;
use App\TypeDiplome\TypeDiplomeHandlerInterface;

final class DaeuHandler //implements TypeDiplomeHandlerInterface
{

    public const TEMPLATE_FOLDER = 'daeu';

    public function supports(string $type): bool
    {
        return $type === 'DAEU'; // TODO: Replace with actual type check for DAEU
    }

    public function calculStructureParcours(Parcours $parcours)
    {
        // TODO: Implement calculStructure() method.
    }

    public function showStructure(Parcours $parcours): array
    {
        // TODO: Implement showStructure() method.
    }

    public function getStructureCompetences(Parcours $parcours)
    {
        // TODO: Implement getStructureCompetences() method.
    }
}
