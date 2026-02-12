<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DTO/Workflow/FieldMetaDto.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 12/02/2026 16:28
 */

namespace App\DTO\Workflow;

class FieldMetaDto
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public readonly string  $name,
        public readonly string  $type,      // 'text'|'textarea'|'checkbox'|'date'|'choice'
        public readonly bool    $required,
        public readonly ?string $label,
        public readonly ?string $help,
        public readonly array   $options = [],
    )
    {
    }
}
