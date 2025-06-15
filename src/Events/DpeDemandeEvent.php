<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Events/DemandeDpeEvent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 30/01/2024 08:53
 */

namespace App\Events;

use App\Entity\DpeDemande;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class DpeDemandeEvent
{
    public const string DPE_DEMANDE_CLOSED = 'demande_dpe.closed';
    public const string DPE_DEMANDE_OPENED = 'demande_dpe.opened';
    public const string DPE_DEMANDE_UPDATED = 'demande_dpe.updated';

    protected DpeDemande $dpeDemande;
    protected UserInterface $user;

    public function __construct(
        DpeDemande $dpeDemande,
        UserInterface|User $user
    ) {
        $this->dpeDemande = $dpeDemande;
        $this->user = $user;
    }

    public function getDpeDemande(): DpeDemande
    {
        return $this->dpeDemande;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
