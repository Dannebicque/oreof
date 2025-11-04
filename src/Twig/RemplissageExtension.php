<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/RemplissageExtension.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Twig;

use App\DTO\Remplissage;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class RemplissageExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('remplissage', $this->remplissage(...), ['is_safe' => ['html']])
        ];
    }

    public function remplissage(Remplissage|float|null $value): string
    {
        if ($value === null) {
            return '<div class="progress sh-2">
                        <div class="progress-bar bg-danger" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">Non complété</div>
                    </div>';
        }

        if ($value instanceof Remplissage) {
            $value = $value->calcul();
        }

        $value = round($value);

        if ($value === 0.0) {
            return '<div class="progress sh-2">
                        <div class="progress-bar bg-danger" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">Non complété</div>
                    </div>';
        }

        if ($value === 100.0) {
            return '<div class="progress sh-2">
                        <div class="progress-bar bg-success" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">Complet</div>
                    </div>';
        }

        return '<div class="progress sh-2">
                    <div class="progress-bar" role="progressbar" aria-valuenow="' . $value . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $value . '%;">' . $value . '%</div>
                </div>';
    }
}
