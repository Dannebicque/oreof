<?php
/*
 * Copyright (c) 2023. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/intranetV3/src/Twig/AppExtension.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 03/01/2023 17:57
 */

namespace App\Twig;

use App\Classes\Configuration;
use App\Entity\Constantes;
use App\Entity\Etudiant;
use App\Entity\Personnel;
use App\Utils\Tools;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use function chr;
use function count;

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
            new TwigFilter('rncp_link', [$this, 'rncpLink'], ['is_safe' => ['html']])
        ];
    }

    public function dateFr(\DateTimeInterface $value): string
    {
        return $value->format('d/m/Y H:i');
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
