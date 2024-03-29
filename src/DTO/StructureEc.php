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

    private bool $withEcts = true;
    private bool $withBcc = true;

    public function __construct(
        ElementConstitutif $elementConstitutif, Parcours $parcours, bool $isBut = false, bool $withEcts = true, bool $withBcc = true)
    {
        $getElement = new GetElementConstitutif($elementConstitutif, $parcours);
        $this->withEcts = $withEcts;
        $this->withBcc = $withBcc;
        $this->raccroche = $getElement->isRaccroche();
        $this->elementRaccroche = $getElement->getElementConstitutif();

        $this->elementConstitutif = $elementConstitutif;
        if ($this->withEcts) {
            $this->heuresEctsEc = new HeuresEctsEc();
            $this->typeMccc = $getElement->getTypeMccc();
            $this->heuresEctsEc->addEc($getElement->getElementConstitutifHeures(), $isBut);
            $this->heuresEctsEc->addEcts($getElement->getEcts());
            $this->mcccs = $getElement->getMcccsCollection();

        }

        if ($this->withBcc) {
            $this->bccs = $getElement->getBccs();
        }
    }

    public function addEcEnfant(?int $idEc, StructureEc $structureEc): void
    {
        if ($idEc !== null) {
            $this->elementsConstitutifsEnfants[$idEc] = $structureEc;
        } else {
            $this->elementsConstitutifsEnfants[] = $structureEc;
        }

        if ($this->withEcts) {
            $this->heuresEctsEcEnfants[] = $structureEc->getHeuresEctsEc();
        }
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
