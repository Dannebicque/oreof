<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DTO/ProcessData.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/09/2023 10:54
 */

namespace App\DTO;

use Symfony\Component\Workflow\Marking;

class ProcessData
{

    public array $validation = [];
    public ?Marking $place = null;
    public array $transitions = [];
    public ?bool $valid = null;
    public \Symfony\Component\Workflow\Definition $definition;

    public function placeTexte(): string
    {
        return array_keys($this->place->getPlaces())[0];
    }

}
