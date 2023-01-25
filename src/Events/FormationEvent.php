<?php

namespace App\Events;

use App\Entity\Formation;

class FormationEvent
{
    public const FORMATION_CREATED = 'formation.created';
    public const FORMATION_UPDATED = 'formation.updated';

    protected Formation $formation;

    public function __construct(Formation $formation)
    {
        $this->formation = $formation;
    }

    public function getFormation(): Formation
    {
        return $this->formation;
    }
}
