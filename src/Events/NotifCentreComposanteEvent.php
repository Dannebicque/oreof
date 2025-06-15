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
use App\Entity\User;

class NotifCentreComposanteEvent
{
    public const string NOTIF_ADD_CENTRE_COMPOSANTE = 'notif.add.centre.composante';

    public const string NOTIF_REMOVE_CENTRE_COMPOSANTE = 'notif.remove.centre.composante';

    public function __construct(
        public Composante $composante,
        public ?User $user,
        public array $droits = []
    ) {
    }
}
