<?php

namespace App\Twig\Components;

use App\Classes\GetElementConstitutif;
use App\Entity\DpeParcours;
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
    public ?DpeParcours $dpeParcours = null;
    public bool $deplacer = false;
    public bool $editable = false;
    public ?Ue $ue = null;
    public ?bool $etatMcccComplet = false;
    public ?bool $isMcccSpecifiques = false;
    public ?bool $texte = false;


    #[PostMount]
    public function mounted(): void
    {
        $this->isMcccSpecifiques = $this->elementConstitutif->isMcccSpecifiques();
        if ($this->elementConstitutif !== null) {
            $getElement = new GetElementConstitutif($this->elementConstitutif, $this->parcours);
            $this->etatMcccComplet = $getElement->getEtatMccc()  === 'Complet';
        }
    }
}
