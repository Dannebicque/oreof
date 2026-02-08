<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/Dto/OptionsCalculStructure.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/01/2026 10:20
 */

namespace App\TypeDiplome\Dto;

class OptionsCalculStructure
{
    public bool $withEcts = true;
    public bool $withBcc = true;
    public bool $dataFromFicheMatiere = true;

    public function __construct(bool $withEcts = true, bool $withBcc = true, bool $dataFromFicheMatiere = true)
    {
        $this->withEcts = $withEcts;
        $this->withBcc = $withBcc;
        $this->dataFromFicheMatiere = $dataFromFicheMatiere;
    }
}
