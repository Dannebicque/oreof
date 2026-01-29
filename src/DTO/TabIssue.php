<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DTO/TabIssue.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 25/01/2026 13:20
 */

namespace App\DTO;

final class TabIssue
{
    public function __construct(
        public readonly string $field,   // ex: "parcours_step2[stageText]"
        public readonly string $label,   // ex: "Texte du stage"
        public readonly string $message, // ex: "Obligatoire si stage = oui"
        public readonly string $level = 'error', // optionnel: error|warning
    )
    {
    }
}
