<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Service/Prototype/TypeDiplomePrototypeDuplicator.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 08/04/2026 16:40
 */

declare(strict_types=1);

namespace App\Service\Prototype;

use App\Entity\TypeDiplome;

final class TypeDiplomePrototypeDuplicator
{
    public function duplicate(TypeDiplome $source): TypeDiplome
    {
        $copy = new TypeDiplome();

        $copy
            ->setLibelle(sprintf('%s - Copie', $source->getLibelle() ?? 'Sans libelle'))
            ->setLibelleCourt($source->getLibelleCourt())
            ->setCodeApogee($source->getCodeApogee() ?? 'X')
            ->setCodifIntermediaire($source->isCodifIntermediaire())
            ->setModalitesAdmission($source->getModalitesAdmission())
            ->setPrerequisObligatoires($source->getPrerequisObligatoires())
            ->setPresentationFormation($source->getPresentationFormation())
            ->setInsertionProfessionnelle($source->getInsertionProfessionnelle())
            ->setSemestreDebut($source->getSemestreDebut() ?? 1)
            ->setSemestreFin($source->getSemestreFin() ?? 2)
            ->setNbUeMin($source->getNbUeMin() ?? 0)
            ->setNbUeMax($source->getNbUeMax() ?? 0)
            ->setNbEctsMaxUe($source->getNbEctsMaxUe() ?? 0)
            ->setNbEcParUe($source->getNbEcParUe() ?? 0)
            ->setModeleMcc($source->getModeleMcc() ?? TypeDiplome::class)
            ->setDebutSemestreFlexible($source->isDebutSemestreFlexible() ?? false)
            ->setHasMemoire($source->isHasMemoire() ?? false)
            ->setHasStage($source->isHasStage() ?? false)
            ->setHasSituationPro($source->isHasSituationPro() ?? false)
            ->setHasProjet($source->isHasProjet() ?? false)
            ->setEctsObligatoireSurEc($source->isEctsObligatoireSurEc() ?? true);

        $copy->setMcccObligatoireSurEc($source->getMcccObligatoireSurEc() ?? true);
        $copy->setControleAssiduite($source->isControleAssiduite() ?? false);

        foreach ($source->getTypeEcs() as $typeEc) {
            $copy->addTypeEc($typeEc);
        }

        foreach ($source->getTypeUes() as $typeUe) {
            $copy->addTypeUe($typeUe);
        }

        foreach ($source->getTypeEpreuves() as $typeEpreuve) {
            $copy->addTypeEpreufe($typeEpreuve);
        }

        return $copy;
    }
}


