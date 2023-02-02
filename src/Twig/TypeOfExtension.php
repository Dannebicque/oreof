<?php

namespace App\Twig;

use App\Entity\ElementConstitutif;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TypeOfExtension extends AbstractExtension
{

    public function getFilters(): array
    {
        return [
            new TwigFilter('isElementConstitutif', [$this, 'isElementConstitutif']),
        ];
    }

    public function isElementConstitutif($value): bool
    {
        return $value instanceof ElementConstitutif;
    }

}
