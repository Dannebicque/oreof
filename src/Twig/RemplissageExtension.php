<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class RemplissageExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('remplissage', [$this, 'remplissage'], ['is_safe' => ['html']])
        ];
    }

    public function remplissage(float $value): string
    {
        $value = round($value, 0);

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
