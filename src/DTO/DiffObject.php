<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DTO/DiffObject.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 11/05/2024 07:59
 */

namespace App\DTO;

class DiffObject {

    public function __construct(
        public string|float|int|null $original,
        public string|float|int|null $new
    )
    {}

    public function isDifferent(): bool
    {
        return $this->original !== $this->new;
    }
}
