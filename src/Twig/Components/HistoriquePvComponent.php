<?php

namespace App\Twig\Components;

use App\Classes\GetDpeParcours;
use App\Classes\GetHistorique;
use App\Entity\Parcours;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent('historique_pv')]
final class HistoriquePvComponent
{
    public ?Parcours $parcours = null;
    public bool $hasPv = false;
    public ?string $fichier = null;

    public function __construct(private GetHistorique $getHistorique)
    {
    }

    #[PostMount]
    public function postMount()
    {
        if ($this->parcours === null) {
            return;
        }
        $dpe = GetDpeParcours::getFromParcours($this->parcours);
        if ($dpe === null) {
            return;
        }

        $historique = $this->getHistorique->getHistoriqueParcoursLastStep($dpe, 'soumis_conseil');

        if ($historique !== null) {//état conseil avant, les deux pour compatibilité
            if (array_key_exists('fichier', $historique->getComplements())) {
                $this->hasPv = true;
                $this->fichier = $historique->getComplements()['fichier'];
            }
        }
    }
}
