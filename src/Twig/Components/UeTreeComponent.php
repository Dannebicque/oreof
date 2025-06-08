<?php

namespace App\Twig\Components;

use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\TypeDiplome;
use App\Entity\Ue;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsLiveComponent('ue_tree')]
final class UeTreeComponent
{
    use DefaultActionTrait;

    public ?Ue $ue = null;
    public ?Parcours $parcours = null;
    public ?TypeDiplome $typeDiplome = null;
    public ?Semestre $semestre = null;
    public bool $isSemestreRaccroche = false;

    #[PostMount]
    public function postMount(): void
    {
        // initialization logic can be placed here
    }

    public function getChildren(): iterable
    {
        return $this->ue?->getUeEnfants() ?? [];
    }
}
