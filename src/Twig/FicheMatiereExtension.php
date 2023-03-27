<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/AppExtension.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Twig;

use App\Entity\FicheMatiere;
use App\Entity\Parcours;
use App\Entity\UserCentre;
use App\Enums\CentreGestionEnum;
use App\Enums\EtatRemplissageEnum;
use App\Utils\Tools;
use DateTimeInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class AppExtension.
 */
class FicheMatiereExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('hasParcours', [$this, 'hasParcours']),
        ];
    }

    public function hasParcours(FicheMatiere $ficheMatiere, Parcours $parcours): bool
    {
       foreach($ficheMatiere->getElementConstitutifs() as $elementConstitutif) {
           if($elementConstitutif->getParcours() === $parcours) {
               return true;
           }
       }

       return false;
    }
}
