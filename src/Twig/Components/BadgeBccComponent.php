<?php

namespace App\Twig\Components;

use App\Classes\GetElementConstitutif;
use App\Entity\DpeParcours;
use App\Entity\ElementConstitutif;
use App\Entity\Parcours;
use App\Entity\Ue;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent('badge_bcc')]
final class BadgeBccComponent
{
    public ?ElementConstitutif $elementConstitutif = null;
    public ?Parcours $parcours = null;
    public ?DpeParcours $dpeParcours = null;
    public bool $deplacer = false;
    public bool $editable = false;
    public ?Ue $ue = null;
    public ?bool $etatBccComplet = false;
    public ?bool $isSynchroBcc = false;
    public ?bool $texte = false;
    public ?bool $isParcoursProprietaire = false;

    #[PostMount]
    public function mounted(): void
    {
        $this->isParcoursProprietaire = $this->elementConstitutif?->getParcours()?->getId() === $this->parcours->getId();

        $getElement = new GetElementConstitutif($this->elementConstitutif, $this->parcours);

        $this->isSynchroBcc = $this->elementConstitutif->isSynchroBcc() && $this->elementConstitutif->getFicheMatiere()?->getParcours()?->getId() !== $this->parcours->getId();
        $this->etatBccComplet = $getElement->getEtatBcc() === 'Complet';
    }
}
