<?php

namespace App\Twig;

use App\TypeDiplome\TypeDiplomeRegistry;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TypeDiplomeExtension extends AbstractExtension
{

    public function __construct(private TypeDiplomeRegistry $typeDiplomeRegistry)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('typeDiplome', [$this, 'typeDiplome'], ['is_safe' => ['html']])
        ];
    }

    public function typeDiplome(string $value): string
    {
        $typeD = $this->typeDiplomeRegistry->getTypeDiplome($value);
        return $typeD !== null ? $typeD->libelle : 'Non défini';
    }
}
