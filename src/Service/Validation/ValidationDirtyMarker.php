<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Service/Validation/ValidationDirtyMarker.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 22/01/2026 07:42
 */

namespace App\Service\Validation;

use App\Entity\ElementConstitutif;
use App\Entity\Semestre;
use App\Entity\Ue;
use App\Enums\ValidationStatusEnum;

class ValidationDirtyMarker
{

    public function markEcDirty(ElementConstitutif $ec): void
    {
        $ec->setValidationDirty(true);
        $ec->setValidationStatus(ValidationStatusEnum::INCOMPLETE);

        $ue = $ec->getUe();
        if ($ue) $this->markUeDirty($ue);
    }

    public function markUeDirty(Ue $ue): void
    {
        $ue->setValidationDirty(true);
        $ue->setValidationStatus(ValidationStatusEnum::INCOMPLETE);

        $s = $ue->getSemestre();
        if ($s) $this->markSemestreDirty($s);
    }

    public function markSemestreDirty(Semestre $s): void
    {
        $s->setValidationDirty(true);
        $s->setValidationStatus(ValidationStatusEnum::INCOMPLETE);
    }
}
