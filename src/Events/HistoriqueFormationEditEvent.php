<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Events/HistoriqueFormationEvent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/08/2023 08:41
 */

namespace App\Events;

use App\Entity\HistoriqueFormation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class HistoriqueFormationEditEvent extends AbstractHistoriqueEvent
{
    public const EDIT_HISTORIQUE_FORMATION = 'edit.historique.formation';

    private HistoriqueFormation $historiqueFormation;

    public function __construct(HistoriqueFormation $historiqueFormation, UserInterface $user, string $etape, string $etat, Request $request)
    {
        parent::__construct($user, $etape, $etat, $request);

        $this->historiqueFormation = $historiqueFormation;
    }

    public function getHistoriqueFormation(): HistoriqueFormation
    {
        return $this->historiqueFormation;
    }
}
