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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class HistoriqueFormationEvent extends AbstractHistoriqueEvent
{
    public const string ADD_HISTORIQUE_FORMATION = 'add.historique.formation';
    public const string ADD_HISTORIQUE_FORMATION_CHANGE_RF = 'add.historique.formation.change_rf';

    private Formation $formation;
    private ?string $fileName;

    /**
     * @param Formation $formation
     */
    public function __construct(Formation $formation, UserInterface $user, string $etape, string $etat, Request $request, ?string $fileName = null)
    {
        parent::__construct($user, $etape, $etat, $request);

        $this->formation = $formation;
        $this->fileName = $fileName;
    }

    public function getFormation(): Formation
    {
        return $this->formation;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }
}
