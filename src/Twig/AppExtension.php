<?php

namespace App\Twig;

use App\Entity\UserCentre;
use App\Enums\CentreGestionEnum;
use App\Enums\EtatRemplissageEnum;
use App\Utils\Tools;
use DateTimeInterface;
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
            new TwigFilter('badgeBoolean', [$this, 'badgeBoolean'], ['is_safe' => ['html']]),
            new TwigFilter('badgeDroits', [$this, 'badgeDroits'], ['is_safe' => ['html']]),
            new TwigFilter('badgeCentre', [$this, 'badgeCentre'], ['is_safe' => ['html']]),
            new TwigFilter('etatRemplissage', [$this, 'etatRemplissage'], ['is_safe' => ['html']])
        ];
    }

    public function badgeBoolean(bool $value): string
    {
        return $value ? '<span class="badge bg-success">Oui</span>' : '<span class="badge bg-danger">Non</span>';
    }

    public function etatRemplissage(array $onglets, int $step): string
    {
        if (array_key_exists($step, $onglets)) {
            return '<span class="state state-'.$onglets[$step]->badge().'" id="onglet_'.$step.'"></span>';
        }

        return '';
    }

    public function badgeDroits(array $droits): string
    {
        $html = '';
        $nbdroits = count($droits);
        foreach ($droits as $droit) {
            if ($nbdroits > 1 && $droit !== 'ROLE_LECTEUR') {
                $html .= '<span class="badge bg-success me-1">' . $droit . '</span>';//RoleEnum::from(strtolower($droit))->libelle()
            }
        }

        return $html;
    }

    public function badgeCentre(UserCentre $userCentre): string
    {
        $html = match ($userCentre->typeCentre()) {
            CentreGestionEnum::CENTRE_GESTION_COMPOSANTE => '<span class="badge bg-success me-1">' . $userCentre->displaySimple() . '</span>',
            CentreGestionEnum::CENTRE_GESTION_ETABLISSEMENT => '<span class="badge bg-warning me-1">' . $userCentre->displaySimple() . '</span>',
            CentreGestionEnum::CENTRE_GESTION_FORMATION => '<span class="badge bg-info me-1">' . $userCentre->displaySimple() . '</span>',
            default => '<span class="badge bg-danger me-1">Inconnu</span>',
        };

        return $html;
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
