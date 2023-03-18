<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/verif/FormationState.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 14/03/2023 14:08
 */

namespace App\Classes\verif;

use App\Entity\Formation;
use App\Enums\RegimeInscriptionEnum;

class FormationState
{
    //todo: comment exploiter dans Formation ?
    private Formation $formation;

    public function valideStep(mixed $value, Formation $formation): bool|array
    {
        $this->formation = $formation;
        $method = 'etatOnglet' . ucfirst($value);

        return $this->$method();
    }

    private function etatOnglet0(): bool|array
    {
        if ($this->formation->getSemestreDebut() === 0) {
            return ['error' => 'Vous devez indiquer le semestre de début de la formation.'];
        }
        return true;
    }

    private function etatOnglet1(): bool|array
    {
        $tab['error'] = [];
        if ($this->formation->getLocalisationMention()->count() === 0) {
            $tab['error'][] = 'Vous devez ajouter au moins une ville.';
        }

        if ($this->formation->getComposantesInscription()->count() === 0) {
            $tab['error'][] = 'Vous devez ajouter au moins une composante pour l\'inscription.';
        }

        if (count($this->formation->getRegimeInscription()) === 0) {
            $tab['error'][] = 'Vous devez indiquer au moins un régime d\'inscription.';
        } else {
            if (in_array(RegimeInscriptionEnum::FC_CONTRAT_PRO, $this->formation->getRegimeInscription(), true) ||
                in_array(RegimeInscriptionEnum::FI_APPRENTISSAGE, $this->formation->getRegimeInscription(), true)) {
                if ($this->formation->getModalitesAlternance() === null || trim($this->formation->getModalitesAlternance()) === '') {
                    $tab['error'][] = 'Vous devez indiquer les modalités d\'alternance.';
                }
            }
        }

        return count($tab['error']) > 0 ? $tab : true;
    }

    private function etatOnglet2(): bool|array
    {
        $tab['error'] = [];
        if ($this->formation->getObjectifsFormation() === null || trim($this->formation->getObjectifsFormation()) === '') {
            $tab['error'][] = 'Vous devez ajouter des objectifs pour la formation.';
        }

        if ($this->formation->getContenuFormation() === null || trim($this->formation->getContenuFormation()) === '') {
            $tab['error'][] = 'Vous devez ajouter le contenu de la formation.';
        }

        if ($this->formation->getResultatsAttendus() === null || trim($this->formation->getResultatsAttendus()) === '') {
            $tab['error'][] = 'Vous devez ajouter preciser les résultats attendus pour la formation.';
        }

        if ($this->formation->getRythmeFormation() === null && ($this->formation->getRythmeFormationTexte() === null || trim($this->formation->getRythmeFormationTexte()) === '')) {
            $tab['error'][] = 'Vous devez indiquer le rythme de formation soit en indiquant dans la liste soit en complétant la zone de saisie.';
        }

        return count($tab['error']) > 0 ? $tab : true;
    }

    private function etatOnglet3(): bool|array
    {
        if ($this->formation->isHasParcours() === null) {
            return ['error' => 'Vous devez indiquez s\'il y a des parcours.'];
        }

        if ($this->formation->isHasParcours() === false) {
            return true;
        }

        if ($this->formation->getParcours()->count() > 0) {
            return true;
        }

        return ['error' => 'Vous devez ajouter au moins un parcours.'];
    }
}
