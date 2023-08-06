<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/HistoriqueExtension.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 19/07/2023 16:56
 */

namespace App\Twig;

use App\Classes\ValidationProcess;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HistoriqueExtension extends AbstractExtension
{
    private array $process;

    public function __construct(
        private ValidationProcess $validationProcess,
    ) {
    }

    public function getFilters(): array
    {
        return [
                new TwigFilter('etapeLabel', [$this, 'etapeLabel']),
                new TwigFilter('etapeIcone', [$this, 'etapeIcone']),
            ];
    }

    public function etapeLabel(string $etape): string
    {
        return $this->validationProcess->getEtapeCle($etape, 'label');
    }

    public function etapeIcone(string $etape): string
    {
        return $this->validationProcess->getEtapeCle($etape, 'icon');
    }
}
