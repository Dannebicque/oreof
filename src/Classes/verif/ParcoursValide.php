<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/verif/ParcoursValide.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 27/08/2023 14:55
 */

namespace App\Classes\verif;

use App\Entity\Parcours;
use App\Entity\TypeDiplome;
use App\Enums\RegimeInscriptionEnum;

class ParcoursValide extends AbstractValide
{
    public array $etat = [];
    public array $bccs = [];

    public function __construct(protected Parcours $parcours, protected TypeDiplome $typeDiplome)
    {
    }

    public function valideParcours(): ParcoursValide
    {
        //onglet 1
        $this->etat['respParcours'] = $this->parcours->getRespParcours() ? self::COMPLET : self::VIDE;
        $this->etat['objectifsParcours'] = $this->nonVide($this->parcours->getObjectifsParcours());
        $this->etat['resultatsAttendus'] = $this->nonVide($this->parcours->getResultatsAttendus());
        $this->etat['contenuParcours'] = $this->nonVide($this->parcours->getContenuFormation());
        $this->etat['rythmeParcours'] = $this->parcours->getRythmeFormation() !== null || $this->nonVide($this->parcours->getRythmeFormationTexte()) ? self::COMPLET : self::VIDE;
        $this->etat['localisation'] = $this->parcours->getLocalisation() ? self::COMPLET : self::VIDE;

        //onglet 2
        $this->etat['modalitesEnseignement'] = $this->parcours->getModalitesEnseignement() ? self::COMPLET : self::VIDE;
        if (($this->parcours->isHasStage() === null || $this->parcours->isHasStage() === false) && $this->typeDiplome->isHasStage() === true) {
            $this->etat['stage'] = self::INCOMPLET;
            $this->etat['stageModalite'] = self::VIDE;
            $this->etat['stageHeures'] = self::VIDE;
        } elseif ($this->parcours->isHasStage() === true) {
            if ($this->parcours->getStageText() === null || trim($this->parcours->getStageText()) === '') {
                $this->etat['stageModalite'] = self::INCOMPLET;
                $this->etat['stage'] = self::INCOMPLET;
            } else {
                $this->etat['stageModalite'] = self::COMPLET;
            }
            if ($this->parcours->getNbHeuresStages() === 0.0 || $this->parcours->getNbHeuresStages() === null) {
                $this->etat['stageHeures'] = self::INCOMPLET;
                $this->etat['stage'] = self::INCOMPLET;
            } else {
                $this->etat['stageHeures'] = self::COMPLET;
            }

            if ($this->etat['stageHeures'] === self::COMPLET && $this->etat['stageModalite'] === self::COMPLET) {
                $this->etat['stage'] = self::COMPLET;
            }
        } else {
            $this->etat['stage'] = self::NON_CONCERNE;
        }

        if (($this->parcours->isHasProjet() === null || $this->parcours->isHasProjet() === false) && $this->typeDiplome->isHasProjet() === true) {
            $this->etat['projet'] = self::INCOMPLET;
            $this->etat['projetModalite'] = self::VIDE;
            $this->etat['projetHeures'] = self::VIDE;
        } elseif ($this->parcours->isHasProjet() === true) {
            if ($this->parcours->getProjetText() === null || trim($this->parcours->getProjetText()) === '') {
                $this->etat['projetModalite'] = self::INCOMPLET;
                $this->etat['projet'] = self::INCOMPLET;
            } else {
                $this->etat['projetModalite'] = self::COMPLET;
            }
            if ($this->parcours->getNbHeuresProjet() === 0.0 || $this->parcours->getNbHeuresProjet() === null) {
                $this->etat['projetHeures'] = self::INCOMPLET;
                $this->etat['projet'] = self::INCOMPLET;
            } else {
                $this->etat['projetHeures'] = self::COMPLET;
            }

            if ($this->etat['projetHeures'] === self::COMPLET && $this->etat['projetModalite'] === self::COMPLET) {
                $this->etat['projet'] = self::COMPLET;
            }
        } else {
            $this->etat['projet'] = self::NON_CONCERNE;
        }

        if (($this->parcours->isHasSituationPro() === null || $this->parcours->isHasSituationPro() === false) && $this->typeDiplome->isHasSituationPro() === true) {
            $this->etat['situationPro'] = self::INCOMPLET;
            $this->etat['situationProModalite'] = self::VIDE;
            $this->etat['situationProHeures'] = self::VIDE;
        } elseif ($this->parcours->isHasSituationPro() === true) {
            if ($this->parcours->getSituationProText() === null || trim($this->parcours->getSituationProText()) === '') {
                $this->etat['situationProModalite'] = self::INCOMPLET;
                $this->etat['situationPro'] = self::INCOMPLET;
            } else {
                $this->etat['situationProModalite'] = self::COMPLET;
            }
            if ($this->parcours->getNbHeuresSituationPro() === 0.0 || $this->parcours->getNbHeuresSituationPro() === null) {
                $this->etat['situationProHeures'] = self::INCOMPLET;
                $this->etat['situationPro'] = self::INCOMPLET;
            } else {
                $this->etat['situationProHeures'] = self::COMPLET;
            }

            if ($this->etat['situationProModalite'] === self::COMPLET && $this->etat['situationProHeures'] === self::COMPLET) {
                $this->etat['situationPro'] = self::COMPLET;
            }
        } else {
            $this->etat['situationPro'] = self::NON_CONCERNE;
        }


        if (($this->parcours->isHasMemoire() === null || $this->parcours->isHasMemoire() === false) && $this->typeDiplome->isHasMemoire() === true) {
            $this->etat['memoire'] = self::INCOMPLET;
            $this->etat['memoireModalite'] = self::VIDE;
        } elseif ($this->parcours->isHasMemoire() === true) {
            if ($this->parcours->getMemoireText() === null || trim($this->parcours->getMemoireText()) === '') {
                $this->etat['memoireModalite'] = self::INCOMPLET;
                $this->etat['memoire'] = self::INCOMPLET;
            } else {
                $this->etat['memoireModalite'] = self::COMPLET;
                $this->etat['memoire'] = self::COMPLET;
            }
        } else {
            $this->etat['memoire'] = self::NON_CONCERNE;
        }

        //onglet 3
        $this->etat['competences'] = $this->parcours->getBlocCompetences()->count() > 0 ? self::COMPLET : self::VIDE;

        foreach ($this->parcours->getBlocCompetences() as $blocCompetence) {
            $this->bccs[$blocCompetence->getId()]['texte'] = $blocCompetence->display();
            $this->bccs[$blocCompetence->getId()]['etat'] = $blocCompetence->getCompetences()->count() > 0 ? self::COMPLET : self::VIDE;
            if ($this->bccs[$blocCompetence->getId()]['etat'] === self::VIDE) {
                $this->etat['competences'] = self::INCOMPLET;
            }
        }

        // onglet 4
        $this->etat['structure'] = $this->valideStructure($this->parcours);

        // onglet 5
        $this->etat['preRequis'] = $this->nonVide($this->parcours->getPrerequis());
        $this->etat['composanteInscription'] = $this->nonVide($this->parcours->getComposanteInscription());
        $this->etat['regimeInscription'] = count($this->parcours->getRegimeInscription()) > 0 ? self::COMPLET : self::VIDE;
        $this->etat['coordSecretariat'] = $this->nonVide($this->parcours->getCoordSecretariat());
        $this->etat['modaliteAlternance'] = self::NON_CONCERNE;
        foreach ($this->parcours->getRegimeInscription() as $regimeInscription) {
            if ($regimeInscription !== RegimeInscriptionEnum::FI && $regimeInscription !== RegimeInscriptionEnum::FC) {
                $this->etat['modaliteAlternance'] = $this->nonVide($this->parcours->getModalitesAlternance());
                $this->etat['regimeInscription'] = $this->etat['modaliteAlternance'] === self::COMPLET ? self::COMPLET : self::INCOMPLET;
            }
        }

        // onglet 6

        $this->etat['poursuitesEtudes'] = $this->nonVide($this->parcours->getPoursuitesEtudes());
        $this->etat['debouches'] = $this->nonVide($this->parcours->getDebouches());
        $this->etat['codeRome'] = count($this->parcours->getCodesRome()) > 0 ? self::COMPLET : self::VIDE;


        return $this;
    }


    private function valideStructure(): array
    {
        //todo: gérer les semestres, UE, ... raccrochés, les UE/EC à choix...
        $structure = [];
        $etatGlobal = self::COMPLET;
        $structure['semestres'] = [];
        foreach ($this->parcours->getSemestreParcours() as $semestreParcour) {

            if ($semestreParcour->getSemestre()?->getSemestreRaccroche() !== null) {
                $sem = $semestreParcour->getSemestre()?->getSemestreRaccroche()?->getSemestre();
            } else {
                $sem = $semestreParcour->getSemestre();
            }

            if ($sem !== null) {
                $hasUe = count($sem->getUes()) === 0 ? self::VIDE : self::COMPLET;
                $structure['semestres'][$semestreParcour->getOrdre()]['ues'] = [];
                foreach ($semestreParcour->getSemestre()->getUes() as $ue) {
                    $structure['semestres'][$semestreParcour->getOrdre()]['ues'][$ue->getOrdre()]['global'] = count($ue->getElementConstitutifs()) === 0 ? self::VIDE : self::COMPLET;
                    $structure['semestres'][$semestreParcour->getOrdre()]['ues'][$ue->getOrdre()]['ecs'] = [];
                    foreach ($ue->getElementConstitutifs() as $ec) {
                        if ($ec->getFicheMatiere() === null || $ec->getMcccs()->count() === 0 || $ec->etatStructure() !== 'Complet' || $ec->getEtatBcc($this->parcours) !== 'Complet') {
                            $structure['semestres'][$semestreParcour->getOrdre()]['ues'][$ue->getOrdre()]['ecs'][$ec->getId()]['global'] = self::INCOMPLET;
                            $structure['semestres'][$semestreParcour->getOrdre()]['ues'][$ue->getOrdre()]['global'] = self::INCOMPLET;
                            $hasUe = self::INCOMPLET;

                            //pour chaque cas indiquer l'erreur
                            if ($ec->getFicheMatiere() === null) {
                                $structure['semestres'][$semestreParcour->getOrdre()]['ues'][$ue->getOrdre()]['ecs'][$ec->getId()]['erreur'][] = 'Fiche matière non renseignée';
                            }

                            if ($ec->getMcccs()->count() === 0) {
                                $structure['semestres'][$semestreParcour->getOrdre()]['ues'][$ue->getOrdre()]['ecs'][$ec->getId()]['erreur'][] = 'MCCC non renseignées';
                            }

                            if ($ec->etatStructure() !== 'Complet') {
                                $structure['semestres'][$semestreParcour->getOrdre()]['ues'][$ue->getOrdre()]['ecs'][$ec->getId()]['erreur'][] = 'Volumes horaires non resnsignés';
                            }

                            if ($ec->getEtatBcc($this->parcours) !== 'Complet') {
                                $structure['semestres'][$semestreParcour->getOrdre()]['ues'][$ue->getOrdre()]['ecs'][$ec->getId()]['erreur'][] = 'BCC incomplet ou non renseignés';
                            }

                        } elseif ($ec->getFicheMatiere() === null && $ec->getMcccs()->count() === 0 && $ec->getHeures() === 'À compléter') {
                            $structure['semestres'][$semestreParcour->getOrdre()]['ues'][$ue->getOrdre()]['ecs'][$ec->getId()]['global'] = self::VIDE;
                            $structure['semestres'][$semestreParcour->getOrdre()]['ues'][$ue->getOrdre()]['global'] = self::INCOMPLET;
                            $hasUe = self::INCOMPLET;
                        } else {
                            $structure['semestres'][$semestreParcour->getOrdre()]['ues'][$ue->getOrdre()]['ecs'][$ec->getId()]['global'] = self::COMPLET;
                        }
                    }
                }
                if ($sem->isNonDispense() === false) {
                    $structure['semestres'][$semestreParcour->getOrdre()]['global'] = $sem->totalEctsSemestre() !== 30 ? self::ERREUR : $hasUe;
                    $structure['semestres'][$semestreParcour->getOrdre()]['erreur'][] = $sem->totalEctsSemestre() !== 30 ? 'Le semestre doit faire 30 ECTS' : '';
                }


            }
        }

        $structure['global'] = $etatGlobal;

        return $structure;
    }

    public function isParcoursValide(): bool
    {
        foreach ($this->etat as $etat) {
            if ($etat !== self::COMPLET) {
                return false;
            }
        }

        return true;
    }
}
