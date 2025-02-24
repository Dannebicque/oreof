<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Entity/Traits/HasBeenEditedTrait.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 30/05/2024 17:09
 */

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait HasBeenEditedTrait {
    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $hasBeenEditedManually = false;

    public function getHasBeenEditedManually(): ?bool
    {
        return $this->hasBeenEditedManually;
    }

    public function setHasBeenEditedManually(?bool $hasBeenEditedManually): void
    {
        $this->hasBeenEditedManually = $hasBeenEditedManually;
    }
}
