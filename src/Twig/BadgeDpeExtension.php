<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/BadgeDpeExtension.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

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
            new TwigFilter('badgeDpe', [$this, 'badgeDpe'], ['is_safe' => ['html']]),
            new TwigFilter('badgeEtatComposante', [$this, 'badgeEtatComposante'], ['is_safe' => ['html']]),
            new TwigFilter('badgeFormation', [$this, 'badgeFormation'], ['is_safe' => ['html']]),
            new TwigFilter('badgeEc', [$this, 'badgeEc'], ['is_safe' => ['html']]),
            new TwigFilter('badge', [$this, 'badge'], ['is_safe' => ['html']])
        ];
    }

    public function badgeEc(array $etatsEc): string
    {
        if (count($etatsEc) === 0) {
            return '<span class="badge bg-secondary me-1">Initialisé</span>';
        }

        $etatsEc = array_keys($etatsEc);
        $html = '';
        foreach ($etatsEc as $etatEc) {
            $html .= '<span class="badge bg-'.EtatDpeEnum::from(strtolower($etatEc))->badge().' me-1">' . EtatDpeEnum::from(strtolower($etatEc))->libelle() . '</span>';
        }

        return $html;
    }

    public function badge(string $texte, string $type): string
    {
          return'<span class="badge bg-'.$type.' me-1">' . $texte . '</span>';
    }

    public function badgeFormation(array $etatsFormation): string
    {
        if (count($etatsFormation) === 0) {
            return '<span class="badge bg-secondary me-1">Initialisé</span>';
        }

        $etatsFormation = array_keys($etatsFormation);
        $html = '';
        foreach ($etatsFormation as $etatFormation) {
            $html .= '<span class="badge bg-'.EtatDpeEnum::from(strtolower($etatFormation))->badge().' me-1">' . EtatDpeEnum::from(strtolower($etatFormation))->libelle() . '</span>';
        }

        return $html;
    }

    public function badgeEtatComposante(array $etatsComposante): string
    {
        $etatsComposante = array_keys($etatsComposante);

        if (count($etatsComposante) === 0) {
            return '<span class="badge bg-danger me-1">Initialisé</span>';
        }
        $html = '';
        foreach ($etatsComposante as $etatComposante) {
            $html .= '<span class="badge bg-'.EtatDpeEnum::from(strtolower($etatComposante))->badge().' me-1">' . EtatDpeEnum::from(strtolower($etatComposante))->libelle() . '</span>';
        }

        return $html;
    }

    public function badgeDpe(array $etatsDpe): string
    {
        if (count($etatsDpe) === 0) {
            return '<span class="badge bg-secondary me-1">Initialisé</span>';
        }
        $etatsDpe = array_keys($etatsDpe);
        $html = '';
        foreach ($etatsDpe as $etatDpe) {
            $html .= '<span class="badge bg-'.EtatDpeEnum::from(strtolower($etatDpe))->badge().' me-1">' . EtatDpeEnum::from(strtolower($etatDpe))->libelle() . '</span>';
        }

        return $html;
    }
}
