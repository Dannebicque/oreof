<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/StructureExtension.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/02/2023 23:19
 */

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class StructureExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('badgeEctsSemestre', [$this, 'badgeEctsSemestre'], ['is_safe' => ['html']]),
            new TwigFilter('badgeEctsUe', [$this, 'badgeEctsUe'], ['is_safe' => ['html']]),
            new TwigFilter('badgeNb', [$this, 'badgeNb'], ['is_safe' => ['html']])
        ];
    }

    public function badgeNb(int $nb): string
    {
        $color = $nb <= 0 ? 'danger' : 'primary';
        $badge = '<span class="badge bg-'.$color.' me-2">%s</span>';
        return sprintf($badge, $nb);
    }

    public function badgeEctsSemestre(float $ects, float $max = 30): string
    {
        $color = $ects === $max ? 'success' : 'danger';
        $badge = '<span class="badge bg-'.$color.' me-2">%s ECTS</span>';
        return sprintf($badge, $ects);
    }

    public function badgeEctsUe(float $ects, float $max = 0): string
    {
        if ($max === 0.0 && $ects === 0.0) {
            return sprintf('<span class="badge bg-warning">%s ECTS</span>', $ects);
        }

        if ($max === 0.0 && $ects !== 0.0) {
            return sprintf('<span class="badge bg-success">%s ECTS</span>', $ects);
        }

        $color = $ects === $max ? 'success' : 'danger';
        $badge = '<span class="badge bg-'.$color.'">%s ECTS</span>';
        return sprintf($badge, $ects);
    }
}
