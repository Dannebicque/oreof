<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/Export/ExportInterface.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 15/11/2023 16:22
 */

namespace App\Classes\Export;

use App\Entity\AnneeUniversitaire;

interface ExportInterface
{
    public function exportLink(AnneeUniversitaire $anneeUniversitaire): string;
}
