<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Events/AddCentreParcoursEvent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/04/2023 19:40
 */

namespace App\Events;

use App\Entity\Etablissement;
use App\Entity\User;

class NotifCentreEtablissementEvent
{
    public const NOTIF_ADD_CENTRE_ETABLISSEMENT = 'notif.add.centre.etablissement';

    public const NOTIF_REMOVE_CENTRE_ETABLISSEMENT = 'notif.remove.centre.etablissement';

    public function __construct(
        public Etablissement $etablissement,
        public ?User $user,
        public array $droits = []
    ) {
    }
}
