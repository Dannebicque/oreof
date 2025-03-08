<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/GetElementConstitutif.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 24/09/2023 16:09
 */

namespace App\Classes;

use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\Parcours;
use App\TypeDiplome\Source\TypeDiplomeInterface;
use Doctrine\Common\Collections\Collection;

class GetElementConstitutif
{
    /* @deprecated */
    private ?bool $isRaccroche = null;

    private ElementConstitutif|FicheMatiere|null $ecSource = null;

    public function __construct(
        private readonly ElementConstitutif $elementConstitutif,
        private readonly Parcours $parcours
    ) {
    }

    public function getElementConstitutif(): ElementConstitutif|FicheMatiere
    {
        //todo: ne plus en dépendre ou résumer à  return $this->elementConstitutif;
        if ($this->ecSource !== null) {
            return $this->ecSource;
        }

        if ($this->isRaccroche() && $this->elementConstitutif->getFicheMatiere() !== null && $this->elementConstitutif->getFicheMatiere()->getParcours() !== null) {
            foreach ($this->elementConstitutif->getFicheMatiere()->getParcours()->getElementConstitutifs() as $ec) {
                if ($ec->getFicheMatiere()?->getId() === $this->elementConstitutif->getFicheMatiere()->getId()) {
                    $this->ecSource = $ec;
                    return $this->ecSource;
                }
            }
        } elseif ($this->elementConstitutif->getEcParent() !== null) {
            $this->ecSource = $this->elementConstitutif->getEcParent();
            return $this->ecSource;
        }
        $this->ecSource = $this->elementConstitutif;
        return $this->ecSource;
    }

    /**
     * @deprecated("Ne plus utiliser une fois la recopie des ECTS sur fiche matière validé")
     */
    public function getMcccs(TypeDiplomeInterface $typeD): array
    {
        if ($this->elementConstitutif->getFicheMatiere()?->isMcccImpose()) {
            return $typeD->getMcccs($this->elementConstitutif->getFicheMatiere());
        }

        if ($this->elementConstitutif->isSynchroMccc() === true && $this->isRaccroche() === true) {
            return $typeD->getMcccs($this->getElementConstitutif());
        }

        return $typeD->getMcccs($this->elementConstitutif);
    }

    /**
     * @deprecated("Ne plus utiliser une fois la recopie des ECTS sur fiche matière validé")
     */
    public function getMcccsCollection(): ?Collection
    {
        if ($this->elementConstitutif->getFicheMatiere()?->isMcccImpose()) {
            return $this->elementConstitutif->getFicheMatiere()?->getMcccs();
        }

        if ($this->elementConstitutif->getEcParent() !== null && $this->elementConstitutif->getEcParent()->isMcccEnfantsIdentique()) {
            return $this->elementConstitutif->getEcParent()?->getMcccs();
        }

        if ($this->elementConstitutif->isSynchroMccc() === true && $this->isRaccroche() === true) {
            return $this->getElementConstitutif()->getMcccs();
        }

        return $this->elementConstitutif->getMcccs();
    }

    public function getMcccsFromFicheMatiere(TypeDiplomeInterface $typeD) : array
    {
        $isMcccImpose = $this->elementConstitutif->getFicheMatiere()?->isMcccImpose();
        // MCCC spécifiques sur EC
        if($this->elementConstitutif->isMcccSpecifiques() && !$isMcccImpose) {
            return $typeD->getMcccs($this->elementConstitutif);
        }

        // EC qui a un parent avec MCCC identiques
        if($this->elementConstitutif->getEcParent()?->isMcccEnfantsIdentique() && !$isMcccImpose) {
            return $typeD->getMcccs($this->elementConstitutif->getEcParent());
        }


        if($this->elementConstitutif->getFicheMatiere()) {
            return $typeD->getMcccs($this->elementConstitutif->getFicheMatiere());
        }

        return $typeD->getMcccs($this->elementConstitutif);
    }

    public function getMcccsFromFicheMatiereCollection() : ?Collection
    {
        $isMcccImpose = $this->elementConstitutif->getFicheMatiere()?->isMcccImpose();
        // MCCC spécifiques sur EC
        if(!$isMcccImpose && $this->elementConstitutif->isMcccSpecifiques()) {
            return $this->elementConstitutif->getMcccs();
        }

        // EC qui a un parent avec MCCC identiques
        if(!$isMcccImpose && $this->elementConstitutif->getEcParent()?->isMcccEnfantsIdentique()) {
            return $this->elementConstitutif->getEcParent()?->getMcccs();
        }


        if($this->elementConstitutif->getFicheMatiere()) {
            return $this->elementConstitutif->getFicheMatiere()?->getMcccs();
        }

        return $this->elementConstitutif->getMcccs();
    }

    public function getTypeMcccFromFicheMatiere()
    {
        $isMcccImpose = $this->elementConstitutif->getFicheMatiere()?->isMcccImpose();

        if($this->elementConstitutif->isMcccSpecifiques() && !$isMcccImpose) {
            return $this->elementConstitutif->getTypeMccc();
        }

        if($this->elementConstitutif->getEcParent()?->isMcccEnfantsIdentique() && !$isMcccImpose) {
            return $this->elementConstitutif->getEcParent()?->getTypeMccc();
        }

        if($this->elementConstitutif->getFicheMatiere()) {
            return $this->elementConstitutif->getFicheMatiere()->getTypeMccc();
        } else {
            return $this->elementConstitutif->getTypeMccc();
        }
    }

    /**
     * @deprecated("Ne plus utiliser une fois la recopie des ECTS sur fiche matière validé")
     */
    public function getEcts(): ?float
    {
        if ($this->elementConstitutif->getFicheMatiere()?->isEctsImpose()) {
            return $this->elementConstitutif->getFicheMatiere()?->getEcts();
        }

        if ($this->isRaccroche() === true && $this->elementConstitutif->isSynchroEcts() === true) {
            return $this->getElementConstitutif()?->getEcts();
        }

        if ($this->elementConstitutif->getEcParent() !== null) {
            return $this->elementConstitutif->getEcParent()->getEcts();
        }

        return $this->elementConstitutif->getEcts();
    }

    public function getFicheMatiereEcts() : float
    {
        $isEctsImpose = $this->elementConstitutif->getFicheMatiere()?->isEctsImpose();

        if($this->elementConstitutif->isEctsSpecifiques() && !$isEctsImpose) {
            return $this->elementConstitutif->getEcts();
        }

        if($this->elementConstitutif->getEcParent() !== null && !$isEctsImpose) {
            return $this->elementConstitutif->getEcParent()->getEcts();
        }

        if($this->elementConstitutif->getFicheMatiere() !== null) {
            return $this->elementConstitutif->getFicheMatiere()->getEcts() ?? 0.0;
        }

        return $this->elementConstitutif->getEcts();

    }

    /**
     * @deprecated("Ne plus utiliser une fois la recopie des ECTS sur fiche matière validé")
     */
    public function getElementConstitutifHeures(): ElementConstitutif|FicheMatiere
    {
        if ($this->elementConstitutif->getFicheMatiere()?->isVolumesHorairesImpose()) {
            return $this->elementConstitutif->getFicheMatiere();
        }

        if ($this->elementConstitutif->getEcParent() !== null && $this->elementConstitutif->getEcParent()->isHeuresEnfantsIdentiques() === true) {
            return $this->elementConstitutif->getEcParent();
        }

        if ($this->isRaccroche() === true && $this->elementConstitutif->isSynchroHeures() === true) {
            return $this->getElementConstitutif();
        }
        return $this->elementConstitutif;
    }

    public function getFicheMatiereHeures() : FicheMatiere|ElementConstitutif
    {
        $ficheMatiere = $this->elementConstitutif->getFicheMatiere() ?? $this->elementConstitutif;
        if($this->elementConstitutif instanceof ElementConstitutif) {
            if($this->elementConstitutif->getEcParent()?->isHeuresEnfantsIdentiques()) {
                if(!$this->elementConstitutif->getFicheMatiere()?->isVolumesHorairesImpose()) {
                    $ficheMatiere = $this->elementConstitutif->getEcParent();
                }
            }
            if($this->elementConstitutif->isHeuresSpecifiques()) {
                $ficheMatiere = $this->elementConstitutif;
            }
        }

        return $ficheMatiere;
    }

    /* @deprecated */
    public function isRaccroche():bool
    {
        if ($this->isRaccroche !== null) {
            return $this->isRaccroche;
        }

        if ($this->elementConstitutif->getFicheMatiere()?->isHorsDiplome()) {
            return true;
        }

        if ($this->elementConstitutif->getFicheMatiere()?->getParcours() === null) {
            return false;
        }

        $this->isRaccroche = $this->elementConstitutif->getFicheMatiere()?->getParcours() !== $this->parcours;
        return $this->isRaccroche;
    }

    public function getEtatsMccc(): ?string
    {
        if ($this->elementConstitutif->getEcParent() !== null && $this->elementConstitutif->getEcParent()->isMcccEnfantsIdentique() === true) {
            return $this->elementConstitutif->getEcParent()->getEtatMccc();
        }

        if ($this->elementConstitutif->getFicheMatiere()?->isMcccImpose()) {
            return $this->elementConstitutif->getFicheMatiere()?->getEtatMccc();
        }

        if ($this->elementConstitutif->isMcccSpecifiques() === true) {
            return $this->elementConstitutif->getEtatMccc();
        }

        return $this->elementConstitutif->getFicheMatiere()?->getEtatMccc();
    }

    public function getEtatStructure(): ?string
    {
        if ($this->elementConstitutif->getFicheMatiere()?->isVolumesHorairesImpose()) {
            return $this->elementConstitutif->getFicheMatiere()?->etatStructure();
        }

        if ($this->elementConstitutif->getEcParent() !== null && $this->elementConstitutif->getEcParent()->isHeuresEnfantsIdentiques() === true) {
            return $this->elementConstitutif->getEcParent()->etatStructure();
        }

        if ($this->elementConstitutif->isHeuresSpecifiques() === true) {
            return $this->elementConstitutif->etatStructure();
        }
        return $this->elementConstitutif->getFicheMatiere()?->etatStructure();
    }

    public function getEtatBcc(): ?string
    {
        // cas du BUT
        if ($this->elementConstitutif->getFicheMatiere()?->getApprentissagesCritiques()->count() > 0) {
            //todo: les Ac doivent être dans la bonne compétence...
            foreach ($this->elementConstitutif->getFicheMatiere()?->getApprentissagesCritiques() as $ac) {
                if ($ac->getNiveau() !== null &&
                    $ac->getNiveau()->getCompetence()?->getNumero() === $this->elementConstitutif->getUe()?->getOrdre()) {
                    return 'Complet';
                }
            }
        }


        if ($this->isRaccroche() === true && $this->elementConstitutif->isSynchroBcc() === true) {
            $ec = $this->getElementConstitutif();
            if ($ec->getCompetences()->count() === 0) {
                return $ec->getFicheMatiere()?->getCompetences()->count() > 0 ? 'Complet' : 'A saisir';
            }
            return $ec->getCompetences()->count() > 0 ? 'Complet' : 'A saisir';
        }

        if ($this->elementConstitutif->getCompetences()->count() === 0) {
            return $this->elementConstitutif->getFicheMatiere()?->getCompetences()->count() > 0 ? 'Complet' : 'A saisir';
        }

        return $this->elementConstitutif->getCompetences()->count() > 0 ? 'Complet' : 'A saisir';

    }

    /**
     * @deprecated("Ne plus utiliser une fois la recopie des ECTS sur fiche matière validé")
     */
    public function getTypeMccc(): ?string
    {
        if ($this->elementConstitutif->getEcParent() !== null && $this->elementConstitutif->getEcParent()->isMcccEnfantsIdentique() === true) {
            return $this->elementConstitutif->getEcParent()->getTypeMccc();
        }

        if ($this->elementConstitutif->getFicheMatiere()?->isMcccImpose()) {
            return $this->elementConstitutif->getFicheMatiere()?->getTypeMccc();
        }

        if ($this->isRaccroche() === true && $this->elementConstitutif->isSynchroMccc() === true) {
            return $this->getElementConstitutif()->getTypeMccc();
        }

        return $this->elementConstitutif->getTypeMccc();
    }

    public function getBccs(): ?Collection
    {
        if ($this->isRaccroche() === true && $this->elementConstitutif->isSynchroBcc() === true) {
            $ec = $this->getElementConstitutif();
            if ($ec->getCompetences()->count() === 0) {
                return $ec->getFicheMatiere()?->getCompetences();
            }
            return $ec->getCompetences();
        }

        if ($this->elementConstitutif->getCompetences()->count() === 0) {
            return $this->elementConstitutif->getFicheMatiere()?->getCompetences();
        }

        return $this->elementConstitutif->getCompetences();
    }

    public function setIsRaccroche(bool $raccroche): void
    {
        $this->isRaccroche = $raccroche;
    }

    public function getEtatMccc(): ?string
    {
        if ($this->elementConstitutif->isMcccSpecifiques()) {
            return $this->elementConstitutif->getEtatMccc();
        }

        if ($this->elementConstitutif->getEcParent() !== null && $this->elementConstitutif->getEcParent()->isMcccEnfantsIdentique() === true) {
            return (new GetElementConstitutif($this->elementConstitutif->getEcParent(), $this->parcours))->getEtatMccc();
        }

        return $this->elementConstitutif->getFicheMatiere()?->getEtatMccc();
    }

    public function isSansHeures(): bool
    {
        return $this->elementConstitutif->isSansHeure() ?? false;
    }
}
