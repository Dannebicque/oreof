<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Events/FormationEvent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 23/01/2023 18:40
 */

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
