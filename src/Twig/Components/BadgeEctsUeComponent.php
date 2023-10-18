<?php

namespace App\Twig\Components;

use App\Classes\GetElementConstitutif;
use App\Classes\GetUeEcts;
use App\Entity\ElementConstitutif;
use App\Entity\Parcours;
use App\Entity\TypeDiplome;
use App\Entity\Ue;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

/*
 * <span id="ects_ue_{{ ue.id }}_{{ parcours.id }}">

                                            </span>
 */

#[AsTwigComponent('badge_ects_ue')]
final class BadgeEctsUeComponent
{
    public ?Ue $ue = null;
    public ?Parcours $parcours = null;
    public null|float|string $ects = null;
    public ?TypeDiplome $typeDiplome = null;
    public null|float|string $maxEcts = null;

    #[PostMount]
    public function mounted(): void
    {
        $this->maxEcts = $this->typeDiplome->getNbEctsMaxUe();
        $this->ects = GetUeEcts::getEcts($this->ue, $this->parcours, $this->typeDiplome);
    }
}
