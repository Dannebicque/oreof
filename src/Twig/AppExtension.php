<?php

namespace App\Twig;

use App\Utils\Tools;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class AppExtension.
 */
class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('tel_format', [$this, 'telFormat']),
            new TwigFilter('mailto', [$this, 'mailto'], ['is_safe' => ['html']]),
            new TwigFilter('dateFr', [$this, 'dateFr'], ['is_safe' => ['html']]),
            new TwigFilter('dateTimeFr', [$this, 'dateTimeFr'], ['is_safe' => ['html']]),
            new TwigFilter('rncp_link', [$this, 'rncpLink'], ['is_safe' => ['html']]),
            new TwigFilter('badgeBoolean', [$this, 'badgeBoolean'], ['is_safe' => ['html']])
        ];
    }

    public function badgeBoolean(bool $value): string
    {
        return $value ? '<span class="badge bg-success">Oui</span>' : '<span class="badge bg-danger">Non</span>';
    }

    public function dateFr(?\DateTimeInterface $value): string
    {
        return $value !== null ? $value->format('d/m/Y') : 'Erreur';
    }

    public function dateTimeFr(?\DateTimeInterface $value): string
    {
        return $value !== null ? $value->format('d/m/Y H:i') : 'Erreur';
    }

    public function rncpLink(?string $code): string
    {
        if (str_starts_with($code, 'rncp')) {
            $code = mb_substr($code, 4, mb_strlen($code));
        }

        return '<a href="https://www.francecompetences.fr/recherche/rncp/'.$code.'" target="_blank">'.$code.' <i class="fal
                            fa-arrow-up-right-from-square"></i></a>&nbsp;';
    }

    public function mailto(?string $email): string
    {
        if (null === $email) {
            return '';
        }

        return '<a href="mailto:'.$email.'" target="_blank">'.$email.' <i class="fal
                            fa-arrow-up-right-from-square"></i></a>&nbsp;';
    }

    public function telFormat(?string $number): ?string
    {
        return Tools::telFormat($number);
    }
}
