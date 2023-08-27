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

class ParcoursValide
{
    public const COMPLET = 'complet';
    public const INCOMPLET = 'incomplet';
    public const VIDE = 'vide';
    public const NON_CONCERNE = 'non_concerne';

    public array $etat = [];
    public array $bccs = [];

    public function __construct()
    {
    }

    public function valide(Parcours $parcours, TypeDiplome $typeDiplome): ParcoursValide
    {
        //onglet 1
        $this->etat['respParcours'] = $parcours->getRespParcours() ? self::COMPLET : self::VIDE;
        $this->etat['objectifsParcours'] = $this->nonVide($parcours->getObjectifsParcours());
        $this->etat['resultatsAttendus'] = $this->nonVide($parcours->getResultatsAttendus());
        $this->etat['contenuParcours'] = $this->nonVide($parcours->getContenuFormation());
        $this->etat['rythmeParcours'] = $parcours->getRythmeFormation() !== null || $this->nonVide($parcours->getRythmeFormationTexte()) ? self::COMPLET : self::VIDE;
        $this->etat['localisation'] = $parcours->getLocalisation() ? self::COMPLET : self::VIDE;

        //onglet 2
        $this->etat['modalitesEnseignement'] = $parcours->getModalitesEnseignement() ? self::COMPLET : self::VIDE;
        if ($parcours->isHasStage() === null && $typeDiplome->isHasStage() === true) {
            $this->etat['stage'] = self::INCOMPLET;
            $this->etat['stageModalite'] = self::VIDE;
            $this->etat['stageHeures'] = self::VIDE;
        } elseif ($parcours->isHasStage() === true) {
            if ($parcours->getStageText() === null || trim($parcours->getStageText()) === '') {
                $this->etat['stageModalite'] = self::INCOMPLET;
                $this->etat['stage'] = self::INCOMPLET;
            } else {
                $this->etat['stageModalite'] = self::COMPLET;
            }
            if ($parcours->getNbHeuresStages() === 0.0 || $parcours->getNbHeuresStages() === null) {
                $this->etat['stageHeures'] = self::INCOMPLET;
                $this->etat['stage'] = self::INCOMPLET;
            } else {
                $this->etat['stageHeures'] = self::COMPLET;
            }

            if ($this->etat['stageHeures'] === self::COMPLET && $this->etat['stageModalite'] === self::COMPLET) {
                $this->etat['stage'] = self::COMPLET;
            }
        } else {
            $this->etat['stage'] = self::COMPLET;
        }

        if ($parcours->isHasProjet() === null && $typeDiplome->isHasProjet() === true) {
            $this->etat['projet'] = self::INCOMPLET;
            $this->etat['projetModalite'] = self::VIDE;
            $this->etat['projetHeures'] = self::VIDE;
        } elseif ($parcours->isHasProjet() === true) {
            if ($parcours->getProjetText() === null || trim($parcours->getProjetText()) === '') {
                $this->etat['projetModalite'] = self::INCOMPLET;
                $this->etat['projet'] = self::INCOMPLET;
            } else {
                $this->etat['projetModalite'] = self::COMPLET;
            }
            if ($parcours->getNbHeuresProjet() === 0.0 || $parcours->getNbHeuresProjet() === null) {
                $this->etat['projetHeures'] = self::INCOMPLET;
                $this->etat['projet'] = self::INCOMPLET;
            } else {
                $this->etat['projetHeures'] = self::COMPLET;
            }

            if ($this->etat['projetHeures'] === self::COMPLET && $this->etat['projetModalite'] === self::COMPLET) {
                $this->etat['projet'] = self::COMPLET;
            }
        } else {
            $this->etat['projet'] = self::COMPLET;
        }

        if ($parcours->isHasSituationPro() === null && $typeDiplome->isHasSituationPro() === true) {
            $this->etat['situationPro'] = self::INCOMPLET;
            $this->etat['situationProModalite'] = self::VIDE;
            $this->etat['situationProHeures'] = self::VIDE;
        } elseif ($parcours->isHasSituationPro() === true) {
            if ($parcours->getSituationProText() === null || trim($parcours->getSituationProText()) === '') {
                $this->etat['situationProModalite'] = self::INCOMPLET;
                $this->etat['situationPro'] = self::INCOMPLET;
            } else {
                $this->etat['situationProModalite'] = self::COMPLET;
            }
            if ($parcours->getNbHeuresSituationPro() === 0.0 || $parcours->getNbHeuresSituationPro() === null) {
                $this->etat['situationProHeures'] = self::INCOMPLET;
                $this->etat['situationPro'] = self::INCOMPLET;
            } else {
                $this->etat['situationProHeures'] = self::COMPLET;
            }

            if ($this->etat['situationProModalite'] === self::COMPLET && $this->etat['situationProHeures'] === self::COMPLET) {
                $this->etat['situationPro'] = self::COMPLET;
            }
        } else {
            $this->etat['situationPro'] = self::COMPLET;
        }


        if ($parcours->isHasMemoire() === null && $typeDiplome->isHasMemoire() === true) {
            $this->etat['memoire'] = self::INCOMPLET;
            $this->etat['memoireModalite'] = self::VIDE;
        } elseif ($parcours->isHasMemoire() === true) {
            if ($parcours->getMemoireText() === null || trim($parcours->getMemoireText()) === '') {
                $this->etat['memoireModalite'] = self::INCOMPLET;
                $this->etat['memoire'] = self::INCOMPLET;
            } else {
                $this->etat['memoireModalite'] = self::COMPLET;
                $this->etat['memoire'] = self::COMPLET;
            }
        } else {
            $this->etat['memoire'] = self::COMPLET;
        }

        //onglet 3
        $this->etat['competences'] = $parcours->getBlocCompetences()->count() > 0 ? self::COMPLET : self::VIDE;

        foreach ($parcours->getBlocCompetences() as $blocCompetence) {
            $this->bccs[$blocCompetence->getId()]['texte'] = $blocCompetence->display();
            $this->bccs[$blocCompetence->getId()]['etat'] = $blocCompetence->getCompetences()->count() > 0 ? self::COMPLET : self::VIDE;
            if ($this->bccs[$blocCompetence->getId()]['etat'] === self::VIDE) {
                $this->etat['competences'] = self::INCOMPLET;
            }
        }

        // onglet 4
        $this->etat['structure'] = $this->valideStructure($parcours);

        // onglet 5
        $this->etat['preRequis'] = $this->nonVide($parcours->getPrerequis());
        $this->etat['composanteInscription'] = $this->nonVide($parcours->getComposanteInscription());
        $this->etat['regimeInscription'] = count($parcours->getRegimeInscription()) > 0 ? self::COMPLET : self::VIDE;
        $this->etat['coordSecretariat'] = $this->nonVide($parcours->getCoordSecretariat());
        $this->etat['modaliteAlternance'] = self::NON_CONCERNE;
        foreach ($parcours->getRegimeInscription() as $regimeInscription) {
            if ($regimeInscription !== RegimeInscriptionEnum::FI && $regimeInscription !== RegimeInscriptionEnum::FC) {
                $this->etat['modaliteAlternance'] = $this->nonVide($parcours->getModalitesAlternance());
                $this->etat['regimeInscription'] = $this->etat['modaliteAlternance'] === self::COMPLET ? self::COMPLET : self::INCOMPLET;
            }
        }


        // onglet 6

        $this->etat['poursuitesEtudes'] = $this->nonVide($parcours->getPoursuitesEtudes());
        $this->etat['debouches'] = $this->nonVide($parcours->getDebouches());
        $this->etat['codeRome'] = count($parcours->getCodesRome()) > 0 ? self::COMPLET : self::VIDE;


        return $this;
    }

    private function nonVide(?string $getObjectifsParcours): string
    {
        if (null !== $getObjectifsParcours && '' !== $getObjectifsParcours) {
            return self::COMPLET;
        }

        return self::VIDE;
    }

    private function valideStructure(Parcours $parcours): array
    {
        $structure = [];
        $etatGlobal = self::COMPLET;

        foreach ($parcours->getSemestreParcours() as $semestreParcour) {
            $structure['semestres'][$semestreParcour->getOrdre()]['global'] = count($semestreParcour->getSemestre()->getUes()) === 0 ? self::VIDE : self::COMPLET;;
            foreach ($semestreParcour->getSemestre()->getUes() as $ue) {
                $structure['semestres'][$semestreParcour->getOrdre()]['ues'][$ue->getOrdre()]['global'] = count($ue->getElementConstitutifs()) === 0 ? self::VIDE : self::COMPLET;
                foreach ($ue->getElementConstitutifs() as $ec) {
                    if ($ec->getFicheMatiere() === null || $ec->getMcccs()->count() === 0 || $ec->etatStructure() !== 'Complet') {
                        $structure['semestres'][$semestreParcour->getOrdre()]['ues'][$ue->getOrdre()]['ecs'][$ec->getId()]['global'] = self::INCOMPLET;
                        $structure['semestres'][$semestreParcour->getOrdre()]['ues'][$ue->getOrdre()]['global'] = self::INCOMPLET;
                        $structure['semestres'][$semestreParcour->getOrdre()]['global'] = self::INCOMPLET;
                    } elseif ($ec->getFicheMatiere() === null && $ec->getMcccs()->count() === 0 && $ec->getHeures() === 'À compléter') {
                        $structure['semestres'][$semestreParcour->getOrdre()]['ues'][$ue->getOrdre()]['ecs'][$ec->getId()]['global'] = self::VIDE;
                        $structure['semestres'][$semestreParcour->getOrdre()]['ues'][$ue->getOrdre()]['global'] = self::INCOMPLET;
                        $structure['semestres'][$semestreParcour->getOrdre()]['global'] = self::INCOMPLET;
                    } else {
                        $structure['semestres'][$semestreParcour->getOrdre()]['ues'][$ue->getOrdre()]['ecs'][$ec->getId()]['global'] = self::COMPLET;
                    }
                }
            }
        }
        $structure['global'] = $etatGlobal;


        return $structure;
    }
}
