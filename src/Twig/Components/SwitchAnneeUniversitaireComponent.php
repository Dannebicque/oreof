<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/Components/SwitchAnneeUniversitaireComponent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 26/01/2023 16:27
 */

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
