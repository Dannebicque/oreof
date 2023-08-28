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
use App\Enums\RegimeInscriptionEnum;

class FormationValide extends AbstractValide
{
    public array $etat = [];

    public function __construct(protected Formation $formation)
    {
    }


    public function valideParcours(array $process): array
    {
        $tParcours = [];
        //vérifier que les parcours sont validés
        foreach ($this->formation->getParcours() as $parcours) {
            $tParcours[$parcours->getId()]['parcours'] = $parcours;
            $tParcours[$parcours->getId()]['etat'] = $parcours->getValide();
        }

        return $tParcours;
    }

    public function valideFormation(): FormationValide
    {
        $this->etat['respFormation'] = $this->formation->getResponsableMention() !== null ? self::COMPLET : self::VIDE;
        $this->etat['localisations'] = $this->formation->getLocalisationMention()->count() > 0 ? self::COMPLET : self::VIDE;
        $this->etat['composantesInscriptions'] = $this->formation->getComposantesInscription()->count() > 0 ? self::COMPLET : self::VIDE;
        $this->etat['regimeInscription'] = count($this->formation->getRegimeInscription()) > 0 ? self::COMPLET : self::VIDE;
        $this->etat['modaliteAlternance'] = self::NON_CONCERNE;
        foreach ($this->formation->getRegimeInscription() as $regimeInscription) {
            if ($regimeInscription !== RegimeInscriptionEnum::FI && $regimeInscription !== RegimeInscriptionEnum::FC) {
                $this->etat['modaliteAlternance'] = $this->nonVide($this->formation->getModalitesAlternance());
                $this->etat['regimeInscription'] = $this->etat['modaliteAlternance'] === self::COMPLET ? self::COMPLET : self::INCOMPLET;
            }
        }
        $this->etat['objectifsFormation'] = $this->nonVide($this->formation->getObjectifsFormation());
        $this->etat['hasParcours'] = $this->formation->isHasParcours() === null ? self::VIDE : self::COMPLET;

        return $this;
    }

    public function isFormationValide(): bool
    {
        foreach ($this->etat as $etat) {
            if ($etat === self::VIDE || $etat === self::INCOMPLET) {
                return false;
            }
        }

        if ($this->formation->isHasParcours() === true) {
            foreach ($this->formation->getParcours() as $parcours)
            {
                $parcoursValide = new ParcoursValide($parcours, $this->formation->getTypeDiplome());
                if ($parcoursValide->isParcoursValide() === false) {
                    return false;
                }

                if (!array_key_exists('valide_parcours_rf', $parcours->getEtatParcours())) {
                    return false;
                }
            }
        }

        return true;
    }
}
