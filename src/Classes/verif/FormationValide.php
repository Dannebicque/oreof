<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/verif/FormationValide.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 23/08/2023 13:53
 */

namespace App\Classes\verif;

use App\Entity\Formation;

class FormationValide
{

    public function valide(Formation $objet, array $process)
    {
        $tParcours = [];
        //vérifier que les parcours sont validés
        foreach ($objet->getParcours() as $parcours) {
            $tParcours[$parcours->getId()]['parcours'] = $parcours;
            $tParcours[$parcours->getId()]['etat'] = $parcours->getValide();
        }

        return $tParcours;
    }
}
