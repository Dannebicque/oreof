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

    public function calcul(): float
    {
        if (0 !== $this->total) {
            return round(($this->score / $this->total) * 100);
        }

        return 0;
    }

    public function setScore(mixed $score): void
    {
        $this->score = $score;
    }

    public function setTotal(mixed $total)
    {
        $this->total = $total;
    }

    public function addRemplissage(Remplissage $remp): void
    {
        $this->score += $remp->score;
        $this->total += $remp->total;
    }

    public function empty()
    {
        return $this->score === 0 && $this->total === 0;
    }
}
