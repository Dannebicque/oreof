<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Events/AddCentreParcoursEvent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/04/2023 19:40
 */

namespace App\Events;

use App\Entity\Composante;
use App\Entity\Profil;
use App\Entity\User;

class NotifCentreComposanteEvent
{
    public const NOTIF_ADD_CENTRE = 'notif.add.centre.composante';

    public const NOTIF_REMOVE_CENTRE = 'notif.remove.centre.composante';

    public function __construct(
        public Composante $composante,
        public ?User $user,
        public Profil $profil,
    ) {
    }
}
