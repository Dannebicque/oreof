<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/verif/ParcoursState.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Classes\verif;

use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\TypeDiplome;
use App\Enums\EtatRemplissageEnum;

class ParcoursState
{
    private Parcours $parcours;
    private ?Formation $formation;
    private ?TypeDiplome $typeDiplome;

    public function setParcours(Parcours $parcours): void
    {
        $this->parcours = $parcours;
        $this->formation = $parcours->getFormation();
        if ($this->formation === null) {
            throw new \Exception('Formation non définie');
        }

        $this->typeDiplome = $this->formation->getTypeDiplome();

        if ($this->typeDiplome === null) {
            throw new \Exception('Type de diplôme non défini');
        }
    }

    public function onglets(): array
    {
        for ($i = 1; $i <= 6; $i++) {
            $methodEmpty = 'isEmptyOnglet' . $i;
            if ($this->$methodEmpty() === true) {
                $onglets[$i] = EtatRemplissageEnum::VIDE;
            } else {
                $method = 'etatOnglet' . $i;
                $onglets[$i] = (($this->$method() === true) && ($this->parcours->getEtatStep($i) === true)) ? EtatRemplissageEnum::COMPLETE : EtatRemplissageEnum::EN_COURS;
            }
        }

        return $onglets;
    }

    public function isEmptyOnglet1(): bool
    {
        return ($this->parcours->getContenuFormation() === null || trim($this->parcours->getContenuFormation()) === '') &&
            ($this->parcours->getResultatsAttendus() === null || trim($this->parcours->getResultatsAttendus()) === '') &&
            ($this->parcours->getRespParcours() === null) &&
            ($this->parcours->getRythmeFormation() === null || trim($this->parcours->getRythmeFormationTexte()) === '');
    }

    public function isEmptyOnglet2(): bool
    {
        $stage = ($this->parcours->isHasStage() === true && $this->parcours->getStageText() !== null && trim($this->parcours->getStageText()) !== '' && $this->parcours->getNbHeuresStages() > 0) || $this->parcours->isHasStage() === false;
        $projet = ($this->parcours->isHasProjet() === true && $this->parcours->getProjetText() !== null && trim($this->parcours->getProjetText()) !== '' && $this->parcours->getNbHeuresProjet() > 0) || $this->parcours->isHasProjet() === false;
        $memoire = ($this->parcours->isHasMemoire() === true && $this->parcours->getMemoireText() !== null && trim($this->parcours->getMemoireText()) !== '') || $this->parcours->isHasProjet() === false;

        return $stage === false && $projet === false && $memoire === false;
    }

    public function isEmptyOnglet3(): bool
    {
        return $this->parcours->getBlocCompetences()->count() === 0 && $this->parcours->getFormation()->getButCompetences()->count() === 0 ;
    }

    public function isEmptyOnglet4(): bool
    {
        return $this->parcours->getSemestreParcours()->count() === 0;
    }

    public function isEmptyOnglet5(): bool
    {
        return ($this->parcours->getPrerequis() === null || trim($this->parcours->getPrerequis()) === '') && ($this->parcours->getCoordSecretariat() === null || trim($this->parcours->getCoordSecretariat()) === '');
    }

    public function isEmptyOnglet6(): bool
    {
        return ($this->parcours->getPoursuitesEtudes() === null || trim($this->parcours->getPoursuitesEtudes()) === '') && ($this->parcours->getDebouches() === null || trim($this->parcours->getDebouches()) === '') && count($this->parcours->getCodesRome()) === 0;
    }


    public function valideStep(mixed $value): bool|array
    {
        $method = 'etatOnglet' . ucfirst($value);

        return $this->$method();
    }

    private function etatOnglet1(): bool|array
    {
        $tab['error'] = [];

        if ($this->parcours->getRespParcours() === null) {
            $tab['error'][] = 'Vous devez indiquer un responsable du parcours.';
        }

        if ($this->parcours->getObjectifsParcours() === null || trim($this->parcours->getObjectifsParcours()) === '') {
            $tab['error'][] = 'Vous devez ajouter des objectifs pour le parcours.';
        }

        if ($this->parcours->getContenuFormation() === null || trim($this->parcours->getContenuFormation()) === '') {
            $tab['error'][] = 'Vous devez ajouter le contenu de la formation.';
        }

        if ($this->parcours->getResultatsAttendus() === null || trim($this->parcours->getResultatsAttendus()) === '') {
            $tab['error'][] = 'Vous devez ajouter preciser les résultats attendus pour la formation.';
        }

        if ($this->parcours->getRythmeFormation() === null && ($this->parcours->getRythmeFormationTexte() === null || trim($this->parcours->getRythmeFormationTexte()) === '')) {
            $tab['error'][] = 'Vous devez indiquer le rythme de formation soit en indiquant dans la liste soit en complétant la zone de saisie.';
        }

        if ($this->parcours->getLocalisation() === null) {
            $tab['error'][] = 'Vous devez indiquer la localisation du parcours.';
        }

        return count($tab['error']) > 0 ? $tab : true;
    }

    private function etatOnglet2(): bool|array
    {
        $tab['error'] = [];

        if ($this->parcours->getModalitesEnseignement() === null) {
            $tab['error'][] = 'Vous devez indiquer la modalité des enseignements.';
        }

        if ($this->parcours->isHasStage() === null && $this->typeDiplome->isHasStage() === true) {
            $tab['error'][] = 'Vous devez indiquer si le parcours comporte des stages.';
        } elseif ($this->parcours->isHasStage() === true) {
            if ($this->parcours->getStageText() === null || trim($this->parcours->getStageText()) === '') {
                $tab['error'][] = 'Vous devez indiquer la modalité des stages.';
            }
            if ($this->parcours->getNbHeuresStages() === 0.0 || $this->parcours->getNbHeuresStages() === null) {
                $tab['error'][] = 'Vous devez indiquer le nombre d\'heures de stages.';
            }
        }

        if ($this->parcours->isHasProjet() === null && $this->typeDiplome->isHasProjet() === true) {
            $tab['error'][] = 'Vous devez indiquer si le parcours comporte des projets tutorés.';
        } elseif ($this->parcours->isHasProjet() === true) {
            if ($this->parcours->getProjetText() === null || trim($this->parcours->getProjetText()) === '') {
                $tab['error'][] = 'Vous devez indiquer la modalité des projets tutorés.';
            }
            if ($this->parcours->getNbHeuresProjet() === 0.0 || $this->parcours->getNbHeuresProjet() === null) {
                $tab['error'][] = 'Vous devez indiquer le nombre d\'heures de projet tutorés.';
            }
        }

        if ($this->parcours->isHasSituationPro() === null && $this->typeDiplome->isHasSituationPro() === true) {
            $tab['error'][] = 'Vous devez indiquer si le parcours comporte des mises en situations professionnelles.';
        } elseif ($this->parcours->isHasSituationPro() === true) {
            if ($this->parcours->getSituationProText() === null || trim($this->parcours->getSituationProText()) === '') {
                $tab['error'][] = 'Vous devez indiquer la modalité des situations professionnelles.';
            }
            if ($this->parcours->getNbHeuresSituationPro() === 0.0 || $this->parcours->getNbHeuresSituationPro() === null) {
                $tab['error'][] = 'Vous devez indiquer le nombre d\'heures de situation professionnelles.';
            }
        }


        if ($this->parcours->isHasMemoire() === null && $this->typeDiplome->isHasMemoire() === true) {
            $tab['error'][] = 'Vous devez indiquer si le parcours comporte un/des TER ou un/des mémoires.';
        } elseif ($this->parcours->isHasMemoire() === true) {
            if ($this->parcours->getMemoireText() === null || trim($this->parcours->getMemoireText()) === '') {
                $tab['error'][] = 'Vous devez indiquer la modalité des mémoires/TER.';
            }
        }

        return count($tab['error']) > 0 ? $tab : true;
    }

    private function etatOnglet3(): bool|array
    {
        $tab['error'] = [];

        if ($this->parcours->getBlocCompetences()->count() === 0 && $this->parcours->getFormation()->getButCompetences()->count() === 0) {
            $tab['error'][] = 'Vous devez ajouter au moins un bloc de compétences.';
        }

        if ($this->parcours->getBlocCompetences()->count() > 0) {
            foreach ($this->parcours->getBlocCompetences() as $bloc) {
                if ($bloc->getCompetences()->count() === 0) {
                    $tab['error'][] = 'Vous devez ajouter au moins une compétence au bloc de compétences "' . $bloc->getLibelle() . '".';
                }
            }
        }

        return count($tab['error']) > 0 ? $tab : true;
    }

    private function etatOnglet4(): bool|array
    {
        $tab['error'] = [];

        if ($this->parcours->getSemestreParcours()->count() === 0) {
            $tab['error'][] = 'Vous devez ajouter au moins un semestre.';
        }

        foreach ($this->parcours->getSemestreParcours() as $semestre) {
            if ($semestre->getSemestre()?->getUes()->count() === 0) {
                $tab['error'][] = 'Vous devez ajouter au moins une UE au semestre "' . $semestre->getSemestre()?->display() . '".';
            }

            foreach ($semestre->getSemestre()?->getUes() as $ue) {
                if ($ue->getElementConstitutifs()->count() === 0) {
                    $tab['error'][] = 'Vous devez ajouter au moins un EC à l\'UE "' . $ue->display() . '".';
                }
            }
        }

        return count($tab['error']) > 0 ? $tab : true;
    }

    private function etatOnglet5(): bool|array
    {
        $tab['error'] = [];

        if ($this->parcours->getPrerequis() === null || trim($this->parcours->getPrerequis()) === '') {
            $tab['error'][] = 'Vous devez indiquer les prérequis.';
        }

        if ($this->parcours->getCoordSecretariat() === null || trim($this->parcours->getCoordSecretariat()) === '') {
            $tab['error'][] = 'Vous devez indiquer les coordonnées du secrétariat.';
        }

        return count($tab['error']) > 0 ? $tab : true;
    }

    private function etatOnglet6(): bool|array
    {
        $tab['error'] = [];

        if ($this->parcours->getPoursuitesEtudes() === null || trim($this->parcours->getPoursuitesEtudes()) === '') {
            $tab['error'][] = 'Vous devez indiquer les poursuites d\'études possibles.';
        }

        if ($this->parcours->getDebouches() === null || trim($this->parcours->getDebouches()) === '') {
            $tab['error'][] = 'Vous devez indiquer les débouchés possibles.';
        }

        if (count($this->parcours->getCodesRome()) === 0) {
            $tab['error'][] = 'Vous devez indiquer au moins un code ROME.';
        }

        return count($tab['error']) > 0 ? $tab : true;
    }
}
