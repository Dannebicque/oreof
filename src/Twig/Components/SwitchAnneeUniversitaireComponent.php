<?php

namespace App\Twig\Components;

use App\Repository\AnneeUniversitaireRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('switch_annee_universitaire')]
final class SwitchAnneeUniversitaireComponent
{
    public array $anneesUniversitaires;

    public function __construct(AnneeUniversitaireRepository $anneeUniversitaireRepository)
    {
        $this->anneesUniversitaires = $anneeUniversitaireRepository->findAll();
    }
}
