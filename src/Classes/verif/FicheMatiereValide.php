<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/verif/FicheMatiereValide.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 27/08/2023 14:55
 */

namespace App\Classes\verif;

use App\DTO\Remplissage;
use App\Entity\FicheMatiere;
use App\Entity\TypeDiplome;

class FicheMatiereValide extends AbstractValide
{
    public array $etat = [];
    public array $bccs = [];

    public function __construct(
        protected FicheMatiere $ficheMatiere, protected TypeDiplome $typeDiplome)
    {
    }

    public function valideFicheMatiere(): FicheMatiereValide
    {
        $this->etat['referent'] = $this->ficheMatiere->getResponsableFicheMatiere() !== null ? self::COMPLET : self::VIDE;
        $this->etat['libelle'] = $this->nonVide($this->ficheMatiere->getLibelle());
        $this->etat['libelleAnglais'] = $this->nonVide($this->ficheMatiere->getLibelleAnglais());
        $this->etat['mutualise'] = $this->ficheMatiere->isEnseignementMutualise() !== null ? self::COMPLET : self::VIDE;
        $this->etat['description'] = $this->nonVide($this->ficheMatiere->getDescription());
        $this->etat['objectifs'] = $this->nonVide($this->ficheMatiere->getObjectifs());
        $this->etat['langueDispense'] = $this->ficheMatiere->getLangueDispense()->count() > 0 ? self::COMPLET : self::VIDE;
        $this->etat['langueSupport'] = $this->ficheMatiere->getLangueSupport()->count() > 0 ? self::COMPLET : self::VIDE;

        return $this;
    }


    public function verifierEtat($etat): bool
    {
        foreach ($etat as $element) {
            if (is_array($element)) {
                if (!$this->verifierEtat($element)) {
                    return false;
                }
            } elseif ($element === self::INCOMPLET || $element === self::VIDE || $element === self::ERREUR) {
                return false;
            }
        }

        return true;
    }

    public function isFicheMatiereValide(): bool
    {
        return $this->verifierEtat($this->etat);
    }

    public function calculPourcentage(): float
    {
        return $this->calcul()->calcul();
    }

    public function calcul(): Remplissage
    {
        $remplissage = new Remplissage();
        return $this->calculRemplissageFromEtat($this->etat, $remplissage);
    }

    public function calculRemplissageFromEtat(array $etat, Remplissage $remplissage): Remplissage
    {
        foreach ($etat as $element) {
            if (is_array($element)) {
                $this->calculRemplissageFromEtat($element, $remplissage);
            } elseif (
                $element === self::COMPLET ||
                $element === self::INCOMPLET ||
                $element === self::VIDE ||
                $element === self::ERREUR
            ) {
                $remplissage->add($element === self::COMPLET ? 1 : 0);
            }
        }

        return $remplissage;
    }
}