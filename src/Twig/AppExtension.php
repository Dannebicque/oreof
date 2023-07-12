<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/AppExtension.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Twig;

use App\Entity\UserCentre;
use App\Enums\CentreGestionEnum;
use App\Utils\Tools;
use DateTimeInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

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
            new TwigFilter('badgeBoolean', [$this, 'badgeBoolean'], ['is_safe' => ['html']]),
            new TwigFilter('badgeDroits', [$this, 'badgeDroits'], ['is_safe' => ['html']]),
            new TwigFilter('badgeCentre', [$this, 'badgeCentre'], ['is_safe' => ['html']]),
            new TwigFilter('etatRemplissage', [$this, 'etatRemplissage'], ['is_safe' => ['html']])
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('displaySort', [$this, 'displaySort'], ['is_safe' => ['html']]),
            new TwigFunction('getDirection', [$this, 'getDirection'], ['is_safe' => ['html']]),
        ];
    }

    public function displaySort(string $field, ?string $sort, ?string $direction): ?string
    {
        if ($field === $sort) {
            return '<i class="fal fa-caret-'.($direction === 'asc' ? 'up' : 'down').' fa-lg"></i>';
        }

        return '<i class="fal fa-sort fa-lg"></i>';
    }

    public function getDirection(string $field, ?string $sort, ?string $direction): ?string
    {
        if ($field === $sort) {
            return $direction === 'asc' ? 'desc' : 'asc';
        }

        return 'asc';
    }

    public function badgeBoolean(bool $value): string
    {
        return $value ? '<span class="badge bg-success">Oui</span>' : '<span class="badge bg-danger">Non</span>';
    }

    public function etatRemplissage(array $onglets, int $step, string $prefix = ''): string
    {
        if (array_key_exists($step, $onglets)) {
            return '<span class="state state-'.$onglets[$step]->badge().'" id="'.$prefix.'_onglet'.$step.'"></span>';
        }

        return '';
    }

    public function badgeDroits(array $droits): string
    {
        $html = '';
        foreach ($droits as $droit) {
            if ($droit !== 'ROLE_LECTEUR') {
                $html .= '<span class="badge bg-success me-1">' . $droit . '</span>';
            }
        }

        return $html;
    }

    public function badgeCentre(UserCentre $userCentre): string
    {
        $droit = count($userCentre->getDroits()) > 0 ? $userCentre->getDroits()[array_key_first($userCentre->getDroits())] : 'Erreur';
        return match ($userCentre->typeCentre()) {
            CentreGestionEnum::CENTRE_GESTION_COMPOSANTE => '<span class="badge bg-success me-1 mb-1 text-wrap">' . $userCentre->displaySimple() . ' ('.$droit.')</span>',
            CentreGestionEnum::CENTRE_GESTION_ETABLISSEMENT => '<span class="badge bg-warning me-1 mb-1 text-wrap">' . $userCentre->displaySimple() . ' ('.$droit.')</span>',
            CentreGestionEnum::CENTRE_GESTION_FORMATION => '<span class="badge bg-info me-1 mb-1 text-wrap">' . $userCentre->displaySimple() . ' ('.$droit.')</span>',
            default => '<span class="badge bg-danger me-1 text-wrap">Inconnu</span>',
        };
    }

    public function dateFr(?DateTimeInterface $value): string
    {
        return $value !== null ? $value->format('d/m/Y') : 'Erreur';
    }

    public function dateTimeFr(?DateTimeInterface $value): string
    {
        return $value !== null ? $value->format('d/m/Y H:i') : 'Erreur';
    }

    public function rncpLink(?string $code): string
    {
        if (str_starts_with($code, 'rncp')) {
            $code = mb_substr($code, 4, mb_strlen($code));
        }

        return '<a href="https://www.francecompetences.fr/recherche/rncp/' . $code . '" target="_blank">' . $code . ' <i class="fal
                            fa-arrow-up-right-from-square"></i></a>&nbsp;';
    }

    public function mailto(?string $email): string
    {
        if (null === $email) {
            return '';
        }

        return '<a href="mailto:' . $email . '" target="_blank">' . $email . ' <i class="fal
                            fa-arrow-up-right-from-square"></i></a>&nbsp;';
    }

    public function telFormat(?string $number): ?string
    {
        return Tools::telFormat($number);
    }
}
