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
use App\Entity\Profil;
use App\Entity\User;

class NotifCentreParcoursEvent
{
    public const NOTIF_ADD_CENTRE = 'notif.add.centre.parcours';

    public const NOTIF_REMOVE_CENTRE = 'notif.remove.centre.parcours';
    public const NOTIF_UPDATE_CENTRE = 'notif.update.centre.parcours';


    public function __construct(
        public Parcours $parcours,
        public ?User    $user,
        public Profil   $profil,
    )
    {
    }
}
