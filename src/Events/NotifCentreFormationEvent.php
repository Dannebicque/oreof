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
use App\Entity\Profil;
use App\Entity\User;

class NotifCentreFormationEvent
{
    public const NOTIF_ADD_CENTRE = 'notif.add.centre.formation';

    public const NOTIF_REMOVE_CENTRE = 'notif.remove.centre.formation';
    public const NOTIF_UPDATE_CENTRE = 'notif.update.centre.formation';

    public function __construct(
        public Formation $formation,
        public ?User $user,
        public Profil $profil,
    ) {
    }
}
