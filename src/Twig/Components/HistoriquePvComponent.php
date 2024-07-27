<?php

namespace App\Twig\Components;

use App\Entity\Parcours;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent('historique_pv')]
final class HistoriquePvComponent
{
    public ?Parcours $parcours = null;
    public bool $hasPv = false;
    public ?string $fichier = null;

    #[PostMount]
    public function postMount() {
        if ($this->parcours === null) {
            return;
        }
        $historique = $this->parcours->getHistoriqueParcours();
        foreach ($historique as $h) {
           if ($h->getEtape() === 'soumis_conseil' || $h->getEtape() === 'conseil') {//état conseil avant, les deux pour compatibilité
               if (array_key_exists('fichier', $h->getComplements())) {
                     $this->hasPv = true;
                     $this->fichier = $h->getComplements()['fichier'];
               }
           }
        }
    }
}
