<?php

namespace App\Twig\Components;

use App\Entity\Formation;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent('historique_pv')]
final class HistoriquePvComponent
{
    public ?Formation $formation = null;
    public bool $hasPv = false;
    public ?string $pv = null;

    #[PostMount]
    public function postMount() {
        $historique = $this->formation->getHistoriqueFormations();
        foreach ($historique as $h) {
           if ($h->getEtape() === 'conseil') {
               if (array_key_exists('fichier', $h->getComplements())) {
                     $this->hasPv = true;
                     $this->pv = $h->getComplements()['fichier'];
               }
           }
        }
    }
}
