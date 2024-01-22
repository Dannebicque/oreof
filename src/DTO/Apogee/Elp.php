<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DTO/Apogee/Elp.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/01/2024 19:30
 */

namespace App\DTO\Apogee;

use App\Enums\Apogee\CodeNatuElpEnum;
use App\Enums\Apogee\TypeVolumeElpEnum;
use DateTime;
use DateTimeInterface;

class Elp
{
    public string $elementPedagogi = '';
    public ?string $codElp = ''; // max 8 caractères
    public string $libCourtElp = ''; // max 25 caractères
    public string $libElp = ''; // max 60 caractères
    public CodeNatuElpEnum $codNatureElp; // max 4 caractères
    public string $codComposante = ''; // max 3 caractères
    public string $temSuspension = 'N'; // max 1 caractère
    public string $motifSuspension = ''; // max 40 caractères
    public string $temModaliteControle = 'O'; // max 1 caractère
    public string $temEnsADistance = ''; // max 1 caractère
    public string $temHorsEtab = 'N'; // max 1 caractère
    public string $temStage = 'N'; // max 1 caractère
    public string $lieu = ''; // max 40 caractères
    public float $nbrCredits = 0;

    /*
     * dateDebutIP	Date
dateFinIP	Date
descriptionElp	String(2000)
volume	Nombre(6,2)
uniteVolume	String(2)
codPeriode	String(2)
     */

    public ?DateTimeInterface $dateDebutIP = null;
    public ?DateTimeInterface $dateFinIP = null;
    public string $descriptionElp = '';
    public float $volume = 0;
    public ?TypeVolumeElpEnum $uniteVolume = null;
    public string $codPeriode = '';


    public function display(): string
    {
        // liste de toutes les propriétés avec leur valeur sur une liste dt
        //récupérer automatiquement la liste des propriétés de l'objet
        $reflector = new \ReflectionClass($this);
        $props = $reflector->getProperties();

        $html = '<table class="table">';
        $html .= '<tr><th>Propriété</th><th>Valeur</th></tr>';
        foreach ($props as $prop) {
            $html .= '<tr>';
            $html .= '<td>' . $prop->getName() . '</td>';
            if ($prop->getValue($this) instanceof \UnitEnum) {
                $html .= '<td>' . $prop->getValue($this)->value . '</td>';
            } elseif ($prop->getValue($this) instanceof DateTimeInterface) {
                $html .= '<td>' . $prop->getValue($this)->format('d/m/Y') . '</td>';
            } else {
                $html .= '<td>' . $prop->getValue($this) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;
    }
}
