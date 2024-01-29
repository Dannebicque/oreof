<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/StructureExtension.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/02/2023 23:19
 */

namespace App\Twig;

use App\Enums\TypeParcoursEnum;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class StructureExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('badgeEctsSemestre', [$this, 'badgeEctsSemestre'], ['is_safe' => ['html']]),
            new TwigFilter('badgeEctsUe', [$this, 'badgeEctsUe'], ['is_safe' => ['html']]),
            new TwigFilter('badgeEcts', [$this, 'badgeEcts'], ['is_safe' => ['html']]),
            new TwigFilter('badgeNb', [$this, 'badgeNb'], ['is_safe' => ['html']]),
            new TwigFilter('badgeTypeParcours', [$this, 'badgeTypeParcours'], ['is_safe' => ['html']])
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('isLas', [$this, 'isLas']),//todo: gérer de manière générique pour LAS, CPI et d'autres peut être
        ];
    }
    public function badgeTypeParcours(?TypeParcoursEnum $typeParcoursEnum = null): string
    {
        if (null === $typeParcoursEnum || $typeParcoursEnum === TypeParcoursEnum::TYPE_PARCOURS_CLASSIQUE) {
            return '';
        }

        $badge = '<span class="badge bg-%s">%s</span>';
        return sprintf($badge, $typeParcoursEnum->getColor(), $typeParcoursEnum->getLabel());
    }

    public function badgeNb(?int $nb): string
    {
        if ($nb === null) {
            return '<span class="badge bg-warning">Erreur</span>';
        }

        $color = $nb <= 0 ? 'danger' : 'primary';
        $badge = '<span class="badge bg-'.$color.' me-2">%s</span>';
        return sprintf($badge, $nb);
    }

    public function badgeEctsSemestre(?float $ects, float $max = 30): string
    {
        if ($ects === null) {
            return '<span class="badge bg-warning">Erreur ECTS</span>';
        }

        $color = $ects === $max ? 'success' : 'danger';
        $badge = '<span class="badge bg-'.$color.' me-2">%s ECTS</span>';
        return sprintf($badge, $ects);
    }

    public function badgeEcts(?float $ects, string $color = 'info'): string
    {
        if ($ects === null) {
            return '<span class="badge bg-warning">Erreur ECTS</span>';
        }

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
