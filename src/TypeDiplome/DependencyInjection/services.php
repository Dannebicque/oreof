<?php
/*
 * Copyright (c) 2022. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/intranetV3/src/Components/PlanCours/DependencyInjection/services.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 18/11/2022 08:54
 */

namespace App\Components\Questionnaire\DependencyInjection;

use App\Components\PlanCours\PlanCoursRegistry;
use App\Components\PlanCours\Source\PlanCoursMatiere;
use App\Components\PlanCours\Source\PlanCoursRessource;
use App\Components\PlanCours\Source\PlanCoursSAE;
use App\TypeDiplome\Source\ButTypeDiplome;
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
};
