<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Events/HistoriqueFormationEvent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/08/2023 08:41
 */

namespace App\Events;

use App\Entity\Formation;
use Symfony\Component\Security\Core\User\UserInterface;

class HistoriqueFormationEvent extends AbstractHistoriqueEvent
{
    public const ADD_HISTORIQUE_FORMATION = 'add.historique.formation';

    private Formation $formation;

    /**
     * @param Formation $formation
     */
    public function __construct(Formation $formation, UserInterface $user, ?string $etape, ?string $etat, ?string $commentaire = '')
    {
        parent::__construct($user, $etape, $etat, $commentaire);

        $this->formation = $formation;
    }

    public function getFormation(): Formation
    {
        return $this->formation;
    }
}
