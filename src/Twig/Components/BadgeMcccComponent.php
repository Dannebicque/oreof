<?php

namespace App\Twig\Components;

use App\Classes\GetElementConstitutif;
use App\Entity\ElementConstitutif;
use App\Entity\Parcours;
use App\Entity\Ue;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent('badge_mccc')]
final class BadgeMcccComponent
{
    public ?ElementConstitutif $elementConstitutif = null;
    public ?ElementConstitutif $elementConstitutifParent = null;
    public ?Parcours $parcours = null;
    public bool $deplacer = false;
    public bool $editable = false;
    public ?Ue $ue = null;
    public ?bool $etatMcccComplet = false;
    public ?bool $isSynchroMccc = false;
    public ?bool $texte = false;


    #[PostMount]
    public function mounted(): void
    {
        if ($this->elementConstitutif !== null) {


        //todo: bug si imposé et synchro activé en même temps ?
        $this->isSynchroMccc = $this->elementConstitutif->isSynchroMccc() && $this->elementConstitutif->getFicheMatiere()?->getParcours()?->getId() !== $this->parcours?->getId();
//dump($this->elementConstitutif->getId());
        if ($this->isSynchroMccc) {
            $raccroche = GetElementConstitutif::isRaccroche($this->elementConstitutif, $this->parcours);
            $ec = GetElementConstitutif::getElementConstitutif($this->elementConstitutif, $raccroche);
//            dump($ec->getId());
            $this->etatMcccComplet = $ec->getEtatMccc() === 'Complet';
        } else {
            $this->etatMcccComplet = $this->elementConstitutif->getEtatMccc() === 'Complet';
        }
        }

    }
}
