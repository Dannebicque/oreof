<?php

namespace App\Components\Questionnaire\DependencyInjection;

use App\TypeDiplome\Source\ButTypeDiplome;
use App\TypeDiplome\Source\DeustTypeDiplome;
use App\TypeDiplome\Source\LicenceProfessionnelleTypeDiplome;
use App\TypeDiplome\Source\LicenceTypeDiplome;
use App\TypeDiplome\Source\MasterMeefTypeDiplome;
use App\TypeDiplome\Source\MasterTypeDiplome;
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
    $services->set(LicenceProfessionnelleTypeDiplome::class)->tag(TypeDiplomeRegistry::TAG_TYPE_DIPLOME);
    $services->set(MasterTypeDiplome::class)->tag(TypeDiplomeRegistry::TAG_TYPE_DIPLOME);
    $services->set(MasterMeefTypeDiplome::class)->tag(TypeDiplomeRegistry::TAG_TYPE_DIPLOME);
    $services->set(DeustTypeDiplome::class)->tag(TypeDiplomeRegistry::TAG_TYPE_DIPLOME);
};
