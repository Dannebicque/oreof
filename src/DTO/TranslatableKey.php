<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/oreofv2/src/DTO/TranslatableKey.php
 * @author davidannebicque
 * @project oreofv2
 * @lastUpdate 22/04/2026 15:07
 */

namespace App\DTO;

class TranslatableKey
{

    public function __construct(
        public string $key,
        public array  $parameters = [],
        public string $domain = 'messages',
    )
    {
    }

}
