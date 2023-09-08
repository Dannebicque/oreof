<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/Source/TypeDiplomeInterface.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\TypeDiplome\Source;

use App\Entity\ElementConstitutif;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\InputBag;

interface TypeDiplomeInterface
{
    public function getMcccs(ElementConstitutif $elementConstitutif): array|Collection;
    public function saveMcccs(ElementConstitutif $elementConstitutif, InputBag $request): void;
}
