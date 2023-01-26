<?php

namespace App\TypeDiplome\Source;

use App\Entity\Formation;
use App\Entity\Parcours;

class MasterMeefTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'master_meef';
    public const TEMPLATE = 'master_meef.html.twig';

    public string $libelle = 'Master MEEF';

    public function initParcours(Parcours $parcours, Formation $formation): void
    {
        // TODO: Implement initParcours() method.
    }
}
