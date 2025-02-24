<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Events/HistoriqueFormationEvent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/08/2023 08:41
 */

namespace App\Events;

use App\Entity\HistoriqueParcours;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class HistoriqueParcoursEditEvent extends AbstractHistoriqueEvent
{
    public const EDIT_HISTORIQUE_PARCOURS = 'edit.historique.parcours';

    private HistoriqueParcours $historiqueParcours;

    public function __construct(HistoriqueParcours $historiqueParcours, UserInterface $user, string $etape, string $etat, Request $request)
    {
        parent::__construct($user, $etape, $etat, $request);

        $this->historiqueParcours = $historiqueParcours;
    }

    public function getHistoriqueParcours(): HistoriqueParcours
    {
        return $this->historiqueParcours;
    }
}
