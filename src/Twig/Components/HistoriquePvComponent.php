<?php

namespace App\Twig\Components;

use App\Classes\GetDpeParcours;
use App\Classes\GetHistorique;
use App\Entity\ChangeRf;
use App\Entity\Parcours;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent('historique_pv')]
final class HistoriquePvComponent
{
    public ?Parcours $parcours = null;
    public ?ChangeRf $changeRf = null;
    public string $type = 'parcours';
    public bool $hasPv = false;
    public ?string $fichier = null;

    public function __construct(private GetHistorique $getHistorique)
    {
    }

    #[PostMount]
    public function postMount()
    {
        if ($this->type === 'parcours') {
            if ($this->parcours === null) {
                return;
            }
            $dpe = GetDpeParcours::getFromParcours($this->parcours);
            if ($dpe === null) {
                return;
            }

            $historique = $this->getHistorique->getHistoriqueParcoursLastStep($dpe, 'soumis_conseil');


        } else if ($this->type === 'change_rf') {
            if ($this->changeRf === null) {
                return;
            }
            $historique = $this->getHistorique->getHistoriqueChangeRfLastStep($this->changeRf, 'changeRf.soumis_conseil');
        } else {
            return;
        }


        if ($historique !== null) {//état conseil avant, les deux pour compatibilité
            if (array_key_exists('fichier', $historique->getComplements())) {
                $this->hasPv = true;
                $this->fichier = $historique->getComplements()['fichier'];
            }
        }
    }
}
