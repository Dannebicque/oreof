<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DTO/StructureEc.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 01/10/2023 10:39
 */

namespace App\DTO;

use App\Classes\GetElementConstitutif;
use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\Parcours;
use Doctrine\Common\Collections\Collection;

use Symfony\Component\Serializer\Annotation\Groups;

class StructureEc
{
    #[Groups(['DTO_json_versioning'])]
    public ElementConstitutif $elementConstitutif;

    #[Groups(['DTO_json_versioning'])]
    public bool $raccroche = false;

    #[Groups(['DTO_json_versioning'])]
    public array $elementsConstitutifsEnfants = [];

    #[Groups(['DTO_json_versioning'])]
    public HeuresEctsEc $heuresEctsEc;

    #[Groups(['DTO_json_versioning'])]
    public ElementConstitutif|FicheMatiere|null $elementRaccroche = null;

    #[Groups(['DTO_json_versioning'])]
    public array $heuresEctsEcEnfants = [];

    #[Groups(['DTO_json_versioning'])]
    public Collection $mcccs;

    #[Groups(['DTO_json_versioning'])]
    public ?string $typeMccc;

    #[Groups(['DTO_json_versioning'])]
    public ?Collection $bccs;


    public function __construct(ElementConstitutif $elementConstitutif, Parcours $parcours, bool $isBut = false)
    {

        $this->raccroche = GetElementConstitutif::isRaccroche($elementConstitutif, $parcours);
        $this->elementRaccroche = GetElementConstitutif::getElementConstitutif($elementConstitutif, $this->raccroche);

        $this->elementConstitutif = $elementConstitutif;
        $this->heuresEctsEc = new HeuresEctsEc();
        $this->typeMccc = GetElementConstitutif::getTypeMccc($elementConstitutif, $this->raccroche);
        $this->heuresEctsEc->addEc(GetElementConstitutif::getElementConstitutifHeures($elementConstitutif, $this->raccroche), $isBut);
        $this->heuresEctsEc->addEcts(GetElementConstitutif::getEcts($elementConstitutif, $this->raccroche));
        $this->mcccs = GetElementConstitutif::getMcccsCollection($elementConstitutif, $this->raccroche);
        $this->bccs = GetElementConstitutif::getBccs($elementConstitutif, $this->raccroche);
    }

    public function addEcEnfant(?int $idEc, StructureEc $structureEc): void
    {
        if($idEc !== null){
            $this->elementsConstitutifsEnfants[$idEc] = $structureEc;
        }else {
            $this->elementsConstitutifsEnfants[] = $structureEc;
        }
        $this->heuresEctsEcEnfants[] = $structureEc->getHeuresEctsEc();
        //gérer pour prendre le max des heures et ects sur tous les enfants de l'EC
    }

    public function getHeuresEctsEc(): HeuresEctsEc
    {
        if (count($this->heuresEctsEcEnfants) > 0) {
            //parcourir le tableau, comparer les objets et retourner celui dont la somme des heures est la plus grande
            foreach ($this->heuresEctsEcEnfants as $heuresEctsEcEnfant) {
                if ($heuresEctsEcEnfant->sommeEcTotalPres() > $this->heuresEctsEc->sommeEcTotalPres()) {
                    $this->heuresEctsEc = $heuresEctsEcEnfant;
                }
            }
        }

        return $this->heuresEctsEc;
    }
}
