<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/TypeDiplomeExtension.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

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
