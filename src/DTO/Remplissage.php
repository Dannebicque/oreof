<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DTO/Remplissage.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 23/08/2023 14:47
 */

namespace App\DTO;

class Remplissage
{
    public int $score = 0;
    public int $total = 0;

    public function add(int $param)
    {
        $this->score += $param;
        $this->total++;
    }
}
