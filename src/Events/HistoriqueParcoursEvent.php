<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Events/HistoriqueParcoursEvent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/08/2023 08:41
 */

namespace App\Events;

use App\Entity\Parcours;
use Symfony\Component\Security\Core\User\UserInterface;

class HistoriqueParcoursEvent extends AbstractHistoriqueEvent
{

    public const ADD_HISTORIQUE_PARCOURS = 'add.historique.parcours';

    private Parcours $parcours;

    /**
     * @param Parcours $parcours
     */
    public function __construct(Parcours $parcours, UserInterface $user, ?string $etape, ?string $etat, ?string $commentaire = '')
    {
        parent::__construct($user, $etape, $etat, $commentaire);

        $this->parcours = $parcours;
    }

    public function getParcours(): Parcours
    {
        return $this->parcours;
    }
}
