<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/Source/AbstractTypeDiplome.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\TypeDiplome\Source;

use App\TypeDiplome\TypeDiplomeRegistry;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractTypeDiplome
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected TypeDiplomeRegistry $typeDiplomeRegistry
    ) {
    }
}
