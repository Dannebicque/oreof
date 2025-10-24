<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Events/FormationEvent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 23/01/2023 18:40
 */

namespace App\Events;

use App\Entity\Parcours;

class ParcoursEvent
{
    public const PARCOURS_CREATED = 'parcours.created';
    public const PARCOURS_UPDATED = 'parcours.updated';

    protected Parcours $parcours;

    public function __construct(Parcours $parcours)
    {
        $this->parcours = $parcours;
    }

    public function getParcours(): Parcours
    {
        return $this->parcours;
    }
}
