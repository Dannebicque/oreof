<?php

namespace App\Twig\Components;

use App\Classes\GetElementConstitutif;
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
    public bool $deplacer = false;
    public bool $editable = false;
    public ?Ue $ue = null;
    public ?bool $etatBccComplet = false;
    public ?bool $isSynchroBcc = false;

    #[PostMount]
    public function mounted(): void
    {
        $this->isSynchroBcc = $this->elementConstitutif->isSynchroBcc() && $this->elementConstitutif->getFicheMatiere()?->getParcours()?->getId() !== $this->parcours->getId();
        if ($this->isSynchroBcc) {
            $raccroche = GetElementConstitutif::isRaccroche($this->elementConstitutif, $this->parcours);
            $ec = GetElementConstitutif::getElementConstitutif($this->elementConstitutif, $raccroche);
            $this->etatBccComplet = $ec->getEtatBcc($this->parcours) === 'Complet';
        } else {
            $this->etatBccComplet = $this->elementConstitutif->getEtatBcc($this->parcours) === 'Complet';
            //todo: faux positif si Compétences mais d'un autre parcours ? Sans que ce soit attaché pour autant ??
        }

    }
}
