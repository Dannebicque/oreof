<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Events/AddCentreParcoursEvent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/04/2023 19:40
 */

namespace App\Events;

use App\Entity\Formation;
use App\Entity\User;

class NotifCentreFormationEvent
{
    public const string NOTIF_ADD_CENTRE_FORMATION = 'notif.add.centre.formation';

    public const string NOTIF_REMOVE_CENTRE_FORMATION = 'notif.remove.centre.formation';

    public function __construct(
        public Formation $formation,
        public ?User $user,
        public array $droits = []
    ) {
    }
}
