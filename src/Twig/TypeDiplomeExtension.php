<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/TypeDiplomeExtension.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Twig;

use App\Entity\TypeDiplome;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TypeDiplomeExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('typeDiplome', [$this, 'typeDiplome'], ['is_safe' => ['html']])
        ];
    }

    public function typeDiplome(?TypeDiplome $value): string
    {
        return ($value !== null && $value->getLibelle() !== null) ? $value->getLibelle() : '<span class="badge bg-warning">Non défini</span>';
    }
}
