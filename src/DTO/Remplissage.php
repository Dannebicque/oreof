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
    public float $pourcentage = 0.0;

    public function add(int $param = 1): void
    {
        $this->score += $param;
        $this->total++;
    }

    public function calcul(): float
    {
        if (0 !== $this->total) {
            $this->pourcentage = round(($this->score / $this->total) * 100);
            return $this->pourcentage;
        }

        return 0;
    }

    public function setScore(mixed $score): void
    {
        $this->score = $score;
    }

    public function setTotal(mixed $total): void
    {
        $this->total = $total;
    }

    public function addRemplissage(Remplissage $remp): void
    {
        $this->score += $remp->score;
        $this->total += $remp->total;
        $this->pourcentage = $this->calcul();
    }

    public function empty() : bool
    {
        return $this->score === 0 && $this->total === 0;
    }

    public function isFull(): bool
    {
        if (0 === $this->total || $this->empty()) {
            return false;
        }


        return $this->score === $this->total;
    }
}
