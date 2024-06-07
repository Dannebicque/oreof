<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/GenereSynthese.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 06/06/2024 20:54
 */

namespace App\Classes;

use App\Entity\CampagneCollecte;
use App\Entity\Composante;
use App\Entity\Parcours;
use App\Repository\ParcoursRepository;
use App\Service\VersioningParcours;
use App\Service\VersioningStructure;
use App\TypeDiplome\TypeDiplomeRegistry;

class GenereSynthese
{

    public function __construct(
        protected ParcoursRepository $parcoursRepository,
        protected TypeDiplomeRegistry $typeDiplomeRegistry,
        protected VersioningParcours $versioningParcours,
        protected VersioningStructure $versioningStructure
    ) {

    }

    public function getSyntheseByComposante(Composante $composante, CampagneCollecte $dpe): array
    {
        $tDemandes = [];

        //récupérer uniquement les DPE ouverts
        $parcours = $this->parcoursRepository->findByTypeValidationAttenteCfvuAndComposante($dpe, 'soumis_central', $composante); //soumis_cfvu

        foreach ($parcours as $parc) {
            $typeD = $this->typeDiplomeRegistry->getTypeDiplome($parc->getFormation()?->getTypeDiplome()?->getModeleMcc());
            // récupérer les demandes de changement et de modification
            $dto = $typeD->calculStructureParcours($parc, true, false);
            $structureDifferencesParcours = $this->versioningParcours->getStructureDifferencesBetweenParcoursAndLastVersion($parc);
            if ($structureDifferencesParcours !== null) {
                $diffStructure = (new VersioningStructure($structureDifferencesParcours, $dto))->calculDiff();
            } else {
                $diffStructure = null;
            }
            $tDemandes[] = ['parcours' => $parc, 'diffStructure' => $diffStructure, 'dto' => $dto];
        }

        return $tDemandes;
    }

    public function getSyntheseByParcours(Parcours $parcours, Composante $composante, CampagneCollecte $dpe): array
    {
        $typeD = $this->typeDiplomeRegistry->getTypeDiplome($parcours->getFormation()?->getTypeDiplome()?->getModeleMcc());
        // récupérer les demandes de changement et de modification
        $dto = $typeD->calculStructureParcours($parcours, true, false);
        $structureDifferencesParcours = $this->versioningParcours->getStructureDifferencesBetweenParcoursAndLastVersion($parcours);
        if ($structureDifferencesParcours !== null) {
            $diffStructure = (new VersioningStructure($structureDifferencesParcours, $dto))->calculDiff();
        } else {
            $diffStructure = null;
        }
        return ['parcours' => $parcours, 'diffStructure' => $diffStructure, 'dto' => $dto];
    }

}
