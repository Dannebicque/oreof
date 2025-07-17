<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Events/HistoriqueParcoursEvent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/08/2023 08:41
 */

namespace App\Events;

use App\Entity\FicheMatiere;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class HistoriqueFicheMatiereEvent extends AbstractHistoriqueEvent
{

    public const ADD_HISTORIQUE_FICHE_MATIERE = 'add.historique.fiche_matiere';

    private FicheMatiere $ficheMatiere;

    public function __construct(FicheMatiere $ficheMatiere, UserInterface $user, string $etape, string $etat, Request $request)
    {
        parent::__construct($user, $etape, $etat, $request);

        $this->ficheMatiere = $ficheMatiere;
    }

    public function getFicheMatiere(): FicheMatiere
    {
        return $this->ficheMatiere;
    }
}
