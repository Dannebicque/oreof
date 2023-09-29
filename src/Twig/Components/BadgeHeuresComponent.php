<?php

namespace App\Twig\Components;

use App\Classes\GetElementConstitutif;
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
    public bool $deplacer = false;
    public bool $editable = false;
    public ?Ue $ue = null;
    public ?bool $etatHeuresComplet = false;
    public ?bool $isSynchroHeures = false;
    public ?bool $texte = false;

    #[PostMount]
    public function mounted(): void
    {
        $this->isSynchroHeures = $this->elementConstitutif->isSynchroHeures() && $this->elementConstitutif->getFicheMatiere()?->getParcours()?->getId() !== $this->parcours->getId();
        if ($this->isSynchroHeures) {
            $raccroche = GetElementConstitutif::isRaccroche($this->elementConstitutif, $this->parcours);
            $ec = GetElementConstitutif::getElementConstitutif($this->elementConstitutif, $raccroche);
            $this->etatHeuresComplet = $ec->etatStructure() === 'Complet';
        } else {
            $this->etatHeuresComplet = $this->elementConstitutif->etatStructure() === 'Complet';
            //todo: faux positif si Compétences mais d'un autre parcours ? Sans que ce soit attaché pour autant ??
        }

    }
}
