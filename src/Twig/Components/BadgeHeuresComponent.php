<?php

namespace App\Twig\Components;

use App\Classes\GetElementConstitutif;
use App\Entity\DpeParcours;
use App\Entity\ElementConstitutif;
use App\Entity\Parcours;
use App\Entity\Ue;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent('badge_heures')]
final class BadgeHeuresComponent
{
    public ?ElementConstitutif $elementConstitutif = null;
    public ?Parcours $parcours = null;
    public ?DpeParcours $dpeParcours = null;
    public bool $deplacer = false;
    public bool $editable = false;
    public ?Ue $ue = null;
    public ?bool $etatHeuresComplet = false;
    public ?bool $isSynchroHeures = false;
    public ?bool $texte = false;

    #[PostMount]
    public function mounted(): void
    {
        if ($this->elementConstitutif->getFicheMatiere() !== null && $this->elementConstitutif->getFicheMatiere()->isVolumesHorairesImpose() === true) {
            $this->etatHeuresComplet = $this->elementConstitutif->getFicheMatiere()->etatStructure() === 'Complet';
            $this->isSynchroHeures = true;
        } else {
            $this->isSynchroHeures = $this->elementConstitutif->isSynchroHeures() && $this->elementConstitutif->getFicheMatiere()?->getParcours()?->getId() !== $this->parcours->getId();
            if ($this->isSynchroHeures) {
                $getElement = new GetElementConstitutif($this->elementConstitutif, $this->parcours);
                $raccroche = $getElement->isRaccroche();
                $ec = $getElement->getElementConstitutifHeures();
                $this->etatHeuresComplet = $ec->etatStructure() === 'Complet';
            } else {
                $this->etatHeuresComplet = $this->elementConstitutif->etatStructure() === 'Complet';
            }
        }
    }
}
