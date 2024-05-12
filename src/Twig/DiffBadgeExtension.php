<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/DiffBadgeExtension.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 11/05/2024 08:03
 */

namespace App\Twig;

use App\DTO\DiffObject;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DiffBadgeExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('displayDiff', [$this, 'displayDiff'], ['is_safe' => ['html']])
        ];
    }

    public function displayDiff(DiffObject $value): string
    {
        if (false === $value->isDifferent()) {
            return $value->original;
        }

        return '<span class="text-danger text-decoration-line-through">'.$value->original.'</span> <span class="text-success">'.$value->new.'</span>';
    }
}
