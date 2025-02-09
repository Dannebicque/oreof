<?php

namespace App\Twig\Components;

use App\Classes\GetElementConstitutif;
use App\Entity\DpeParcours;
use App\Entity\ElementConstitutif;
use App\Entity\Parcours;
use App\Entity\Ue;
use App\Repository\ElementConstitutifRepository;
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
    public ?bool $isHeuresSpecifiques = false;
    public ?bool $isParcoursProprietaire = false;

    public ?bool $texte = false;

    #[PostMount]
    public function mounted(): void
    {
        $this->isParcoursProprietaire = $this->elementConstitutif->getFicheMatiere()?->getParcours()?->getId() === $this->parcours->getId();
        $this->isHeuresSpecifiques = $this->elementConstitutif->isHeuresSpecifiques();
        if ($this->elementConstitutif->isHeuresSpecifiques() === true) {
            $this->etatHeuresComplet = $this->elementConstitutif->etatStructure() === 'Complet';
        } elseif ($this->elementConstitutif->getFicheMatiere() !== null &&
            ($this->elementConstitutif->getFicheMatiere()->isVolumesHorairesImpose() === true || $this->elementConstitutif->isHeuresSpecifiques() !== true)) {
            $this->etatHeuresComplet = $this->elementConstitutif->getFicheMatiere()->etatStructure() === 'Complet';
        } else {
            $this->etatHeuresComplet = $this->elementConstitutif->etatStructure() === 'Complet';
        }
    }
}
