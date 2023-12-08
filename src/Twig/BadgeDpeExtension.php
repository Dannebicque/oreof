<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/BadgeDpeExtension.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Twig;

use App\Entity\FicheMatiere;
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
            new TwigFilter('badgeFiche', [$this, 'badgeFiche'], ['is_safe' => ['html']]),
            new TwigFilter('badge', [$this, 'badge'], ['is_safe' => ['html']]),
            new TwigFilter('badgeValide', [$this, 'badgeValide'], ['is_safe' => ['html']]),
            new TwigFilter('displayErreurs', [$this, 'displayErreurs'], ['is_safe' => ['html']]),
            new TwigFilter('isFicheValidable', [$this, 'isFicheValidable'], ['is_safe' => ['html']])
        ];
    }

    public function isFicheValidable(FicheMatiere $fiche, string $type): string
    {
        if ($fiche->remplissage() < 100.0) {
            return 'disabled';
        }

        switch ($type) {
            case 'formation':
                return in_array('transmis_rf', $fiche->getEtatFiche()) || in_array('en_cours_redaction', $fiche->getEtatFiche()) ? '' : 'disabled';
            case 'parcours':
                return in_array('en_cours_redaction', $fiche->getEtatFiche()) || count($fiche->getEtatFiche()) === 0 ? '' : 'disabled';
            case 'dpe':
                return in_array('transmis_dpe', $fiche->getEtatFiche()) || in_array('transmis_rf', $fiche->getEtatFiche()) || in_array('en_cours_redaction', $fiche->getEtatFiche()) ? '' : 'disabled';
            default:
                return 'disabled';
        }
    }

    public function displayErreurs(?array $erreurs = []): string
    {
        if (null === $erreurs || 0 === count($erreurs)) {
            return '';
        }

        //retirer les cellules vides du tableau erreurs
        $erreurs = array_filter($erreurs, function ($erreur) {
            return !empty($erreur);
        });


        $texte = '<ul>';
        foreach ($erreurs as $erreur) {
            $texte .= '<li>' . $erreur . '</li>';
        }
        $texte .= '</ul>';
        return '<i class="fal fa-question-circle ms-1"
                   data-controller="tooltip"
                   data-tooltip-placement-value="bottom"
                   aria-label="' . $texte . '"
                   data-bs-original-title="' . $texte . '"></i>';
    }

    public function badgeEc(array $etatsEc): string
    {
        return $this->displayDpeBadge($etatsEc);
    }

    public function badge(string $texte, string $type): string
    {
        return '<span class="badge bg-' . $type . ' me-1">' . $texte . '</span>';
    }

    public function badgeValide(?string $etat): string
    {
        return match ($etat) {
            'complet' => '<span class="badge bg-success me-1">Complet</span>',
            'incomplet' => '<span class="badge bg-warning me-1">Incomplet</span>',
            'erreur' => '<span class="badge bg-danger me-1">Erreur de saisie</span>',
            'vide' => '<span class="badge bg-danger me-1">Non complété</span>',
            'non_concerne' => '<span class="badge bg-info me-1">Non concerné</span>',
            null => '<span class="badge bg-warning me-1">NULL?</span>',
        };
    }

    public function badgeFormation(array $etatsFormation): string
    {
        return $this->displayDpeBadge($etatsFormation);
    }

    public function badgeEtatComposante(array $etatsComposante): string
    {
        $etatsComposante = array_keys($etatsComposante);

        return $this->displayDpeBadge($etatsComposante);
    }

    public function badgeDpe(array $etatsDpe): string
    {
        return $this->displayDpeBadge($etatsDpe);
    }

    public function badgeFiche(array $etatFiche): string
    {
        return $this->displayDpeBadge($etatFiche);
    }

    /**
     * @param array $etatsEc
     * @return string
     */
    private function displayDpeBadge(array $etatsEc): string
    {
        if (count($etatsEc) === 0) {
            return '<span class="badge bg-secondary me-1">Initialisé</span>';
        }

        $etatsEc = array_keys($etatsEc);
        $html = '';
        foreach ($etatsEc as $etatEc) {
            $html .= '<span class="badge bg-' . EtatDpeEnum::from(strtolower($etatEc))->badge() . ' me-1">' . EtatDpeEnum::from(strtolower($etatEc))->libelle() . '</span>';
        }

        return $html;
    }
}
