<?php

namespace App\Twig;

use App\Enums\EtatDpeEnum;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class AppExtension.
 */
class BadgeDpeExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('badgeDpe', [$this, 'badgeDpe'], ['is_safe' => ['html']])
        ];
    }

    public function badgeDpe(array $etatsDpe): string
    {
        $etatsDpe = array_keys($etatsDpe);
        $html = '';
        foreach ($etatsDpe as $etatDpe) {
                $html .= '<span class="badge bg-secondary me-1">' . EtatDpeEnum::from(strtolower($etatDpe))->libelle() . '</span>';
        }

        return $html;
    }
}
