<?php

namespace App\TypeDiplome\Source;

use App\Entity\Formation;
use App\Entity\Parcours;

class LicenceProfessionnelleTypeDiplome extends AbstractTypeDiplome implements TypeDiplomeInterface
{
    public const SOURCE = 'licence_professionnelle';
    public const TEMPLATE = 'lp.html.twig';

    public string $libelle = 'Licence Professionnelle';

    public function initParcours(Parcours $parcours, Formation $formation): void
    {
        // TODO: Implement initParcours() method.
    }
}
