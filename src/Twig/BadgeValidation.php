<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/BadgeValidation.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 22/01/2026 08:01
 */

namespace App\Twig;

use App\Entity\ValidationIssue;
use App\Enums\ValidationStatusEnum;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class BadgeValidation extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('badgeValidationLong', $this->badgeValidationLong(...), ['is_safe' => ['html']]),
            new TwigFilter('badgeValidationShort', $this->badgeValidationShort(...), ['is_safe' => ['html']]),
            new TwigFilter('displayMessage', $this->displayMessage(...), ['is_safe' => ['html']])
        ];
    }

    public function displayMessage(ValidationIssue $issue): string
    {

        switch ($issue->getRuleCode()) {
            case 'EC_MISSING':
                return $issue->getMessage() . '(' . $issue->getPayload()['ec'] . ')';
            case 'UE_MISSING':
                return $issue->getMessage() . '(' . $issue->getPayload()['ue'] . ')';
            case 'MCCC_MISSING':
                return $issue->getMessage() . '(' . $issue->getPayload()['ec'] . ')';
            case 'BCC_INCOMPLETE':
                return $issue->getMessage() . '(' . $issue->getPayload()['ec'] . ')';
            case 'ECTS_INVALID':
                return $issue->getMessage() . '(' . $issue->getPayload()['ec'] . ')';
            case 'FICHE_MATIERE_MISSING':
                return $issue->getMessage() . '(' . $issue->getPayload()['ec'] . ', ' . $issue->getPayload()['ue'] . ' )';
            default:
                return $issue->getMessage();
        }
    }

    public function badgeValidationShort(ValidationStatusEnum $status, string $size = '1.5'): string
    {
        return match ($status) {
            ValidationStatusEnum::VALID => '<span class="inline-block w-' . $size . ' h-' . $size . ' rounded-full bg-green-400"></span>',
            ValidationStatusEnum::INVALID => '<span class="inline-block w-' . $size . ' h-' . $size . ' rounded-full bg-red-400"></span>',
            ValidationStatusEnum::INCOMPLETE => '<span class="inline-block w-' . $size . ' h-' . $size . ' rounded-full bg-orange-300"></span>',
            ValidationStatusEnum::NA => '<span class="inline-block w-' . $size . ' h-' . $size . ' rounded-full bg-gray-400"></span>',
        };
    }

    public function badgeValidationLong(ValidationStatusEnum $status): string
    {
        return match ($status) {
            ValidationStatusEnum::VALID => '<span class="inline-block px-2 py-0.5 rounded-full bg-green-600 text-white text-sm">● Conforme aux règles</span>',
            ValidationStatusEnum::INVALID => '<span class="inline-block px-2 py-0.5 rounded-full bg-red-600 text-white text-sm">● Non conforme aux règles</span>',
            ValidationStatusEnum::INCOMPLETE => '<span class="inline-block px-2 py-0.5 rounded-full bg-yellow-300 text-gray-800 text-sm">● Incomplet</span>',
            ValidationStatusEnum::NA => '<span class="inline-block px-2 py-0.5 rounded-full bg-gray-400 text-white text-sm">Non applicable</span>',
        };
    }

}
