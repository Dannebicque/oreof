<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Service/FicheMatiere/FicheMatiereTabRegistry.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 14/02/2026 22:01
 */

namespace App\Service\FicheMatiere;

use App\Form\FormationStep1Type;
use App\Form\FormationStep2Type;
use App\Form\FormationStep3Type;

final class FicheMatiereTabRegistry
{
    public const TABS = ['identite', 'presentation', 'volumes_horaires', 'mccc', 'mutualisation'];

    public static function assertTab(string $tabKey): void
    {
        if (!in_array($tabKey, self::TABS, true)) {
            throw new \InvalidArgumentException('Unknown tab: ' . $tabKey);
        }
    }

    public static function formTypeFor(string $tabKey): string
    {
        return match ($tabKey) {
            'localisation' => FormationStep1Type::class,
            'presentation' => FormationStep2Type::class,
            'structure' => FormationStep3Type::class,

        };
    }

    public static function validationGroupsFor(string $tabKey): array
    {
        // si tu veux des contraintes spécifiques par tab
        return ['Default', 'tab_' . $tabKey];
    }
}
