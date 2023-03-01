<?php

namespace App\Twig;

use App\TypeDiplome\TypeDiplomeRegistry;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TypeDiplomeExtension extends AbstractExtension
{

    public function __construct(private readonly TypeDiplomeRegistry $typeDiplomeRegistry)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('typeDiplome', [$this, 'typeDiplome'], ['is_safe' => ['html']])
        ];
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    public function typeDiplome(string $value): string
    {
        $typeD = $this->typeDiplomeRegistry->getTypeDiplome($value);
        return $typeD !== null ? $typeD->libelle : 'Non défini';
    }
}
