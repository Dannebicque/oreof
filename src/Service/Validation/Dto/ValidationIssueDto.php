<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Service/Validation/Dto/ValidationIssueDto.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 22/01/2026 07:55
 */

namespace App\Service\Validation\Dto;

use App\Enums\ValidationStatusEnum;

readonly class ValidationIssueDto
{
    public function __construct(
        public string                $scopeType,   // 'semestre' | 'ue' | 'ec' | 'mccc'
        public int                   $scopeId,  // ID de l'objet concerné
        public string                $ruleCode,     // ex: MCCC_MISSING
        public string                $severity,     // 'error' | 'warning' | 'info'
        public string                $message,      // message court UI
        public array                 $payload = [],  // données techniques (heures, seuils, etc.)
        public ?ValidationStatusEnum $status = null
    )
    {
    }
}
