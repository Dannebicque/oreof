<?php

namespace App\TypeDiplome\Source;

use App\Entity\Formation;
use App\Entity\Parcours;

class MasterTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'master';
    public const TEMPLATE = 'master.html.twig';

    public string $libelle = 'Master';

    public function initParcours(Parcours $parcours, Formation $formation): void
    {
        // TODO: Implement initParcours() method.
    }
}
