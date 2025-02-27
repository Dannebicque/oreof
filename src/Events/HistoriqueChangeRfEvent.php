<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Events/HistoriqueFormationEvent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/08/2023 08:41
 */

namespace App\Events;

use App\Entity\ChangeRf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class HistoriqueChangeRfEvent extends AbstractHistoriqueEvent
{
    public const ADD_HISTORIQUE_CHANGE_RF = 'add.historique.formation.change_rf';

    private ChangeRf $changeRf;
    private ?string $fileName;

    public function __construct(ChangeRf $changeRf, UserInterface $user, string $etape, string $etat, Request $request, ?string $fileName = null)
    {
        parent::__construct($user, $etape, $etat, $request);

        $this->changeRf = $changeRf;
        $this->fileName = $fileName;
    }

    public function getChangeRf(): ChangeRf
    {
        return $this->changeRf;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }
}
