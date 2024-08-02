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
use App\Classes\ValidationProcessFicheMatiere;
use App\Entity\HistoriqueFicheMatiere;
use App\Entity\HistoriqueFormation;
use App\Entity\HistoriqueParcours;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HistoriqueExtension extends AbstractExtension
{
    private array $process;
    private array $traductions = [
        'conseil' => 'soumis_conseil',
        'publication' => 'valide_a_publier',
        'cfvu' => 'soumis_cfvu',
        'ses' => 'soumis_central',
        'dpe' => 'soumis_dpe_composante',
        'parcours' => 'en_cours_redaction',
        'parcours_rf' => 'soumis_parcours',
    ];

    public function __construct(
        private ValidationProcess             $validationProcess,
        private ValidationProcessFicheMatiere $validationProcessFicheMatiere,
    )
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('etapeLabel', [$this, 'etapeLabel']),
            new TwigFilter('etapeParams', [$this, 'etapeParams']),
            new TwigFilter('etapeIcone', [$this, 'etapeIcone']),
        ];
    }

    public function etapeLabel(string $etape, string $process = 'formation'): string
    {
        if ($process === 'formation' || $process === 'parcours') {
            if (array_key_exists($etape, $this->traductions)) {
                $etape = $this->traductions[$etape];
            }
            return $this->validationProcess->getEtapeCle($etape, 'label');
        }
        return $this->validationProcessFicheMatiere->getEtapeCle($etape, 'label');
    }

    public function etapeParams(HistoriqueParcours|HistoriqueFormation|HistoriqueFicheMatiere $historique): array
    {
        if ($historique instanceof HistoriqueParcours) {
            return
                ['%parcours%' => $historique->getParcours()?->getDisplay(),
                    '%date%' => $historique->getDate()?->format('d/m/Y'),
                    '%formation%' => $historique->getParcours()?->getFormation()?->getDisplayLong()];
        }

        if ($historique instanceof HistoriqueFormation) {
            return [
                '%formation%' => $historique->getFormation()?->getDisplayLong(),
                '%date%' => $historique->getDate()?->format('d/m/Y'),
            ];
        }

        if ($historique instanceof HistoriqueFicheMatiere) {
            return [
                '%formation%' => $historique->getFicheMatiere()?->getLibelle(),
                '%date%' => $historique->getDate()?->format('d/m/Y'),
            ];
        }
    }

    public function etapeIcone(string $etape, string $process = 'formation'): string
    {
        if ($etape === 'change_rf_co' || $etape === 'change_rf') {
            return 'fal fa-repeat';
        }

        if ($process === 'formation' || $process === 'parcours') {
            if (array_key_exists($etape, $this->traductions)) {
                $etape = $this->traductions[$etape];
            }
            return $this->validationProcess->getEtapeCle($etape, 'icon');
        }
        return $this->validationProcessFicheMatiere->getEtapeCle($etape, 'icon');
    }
}
