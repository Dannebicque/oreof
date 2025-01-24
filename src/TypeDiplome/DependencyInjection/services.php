<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/DependencyInjection/services.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 07/03/2023 15:17
 */

namespace App\Components\Questionnaire\DependencyInjection;

use App\TypeDiplome\Source\ButTypeDiplome;
use App\TypeDiplome\Source\LicenceTypeDiplome;
use App\TypeDiplome\Source\MeefTypeDiplome;
use App\TypeDiplome\TypeDiplomeRegistry;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();
    $services->defaults()
        ->private()
        ->autowire()
        ->autoconfigure(false);

    $services->set(ButTypeDiplome::class)->tag(TypeDiplomeRegistry::TAG_TYPE_DIPLOME);
    $services->set(LicenceTypeDiplome::class)->tag(TypeDiplomeRegistry::TAG_TYPE_DIPLOME);
    $services->set(MeefTypeDiplome::class)->tag(TypeDiplomeRegistry::TAG_TYPE_DIPLOME);
};
