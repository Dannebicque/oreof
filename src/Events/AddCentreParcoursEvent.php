<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Events/AddCentreParcoursEvent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/04/2023 19:40
 */

namespace App\Events;

use App\Entity\Parcours;
use App\Entity\User;

class AddCentreParcoursEvent
{
    public const ADD_CENTRE_PARCOURS = 'add.centre.parcours';

    public const REMOVE_CENTRE_PARCOURS = 'remove.centre.parcours';

    public function __construct(
        public Parcours $parcours,
        public array $droits = [],
        public ?User $user = null
    ) {
    }
}
