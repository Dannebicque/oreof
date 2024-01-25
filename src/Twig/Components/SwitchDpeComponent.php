<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/Components/SwitchAnneeUniversitaireComponent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 26/01/2023 16:27
 */

namespace App\Twig\Components;

use App\Repository\DpeRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('switch_dpe')]
final class SwitchDpeComponent
{
    public array $dpes;

    public function __construct(DpeRepository $dpeRepository)
    {
        $this->dpes = $dpeRepository->findAll();
    }
}
