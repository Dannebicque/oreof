<?php
/*
 * Copyright (c) 2025. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Events/McccUpdateEvent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 07/03/2025 21:01
 */

namespace App\Events;

use App\Entity\ElementConstitutif;
use App\Entity\Parcours;
use Symfony\Contracts\EventDispatcher\Event;

class McccUpdateEvent extends Event
{
    public const UPDATE_MCCC = 'mccc.update';

    public ElementConstitutif $elementConstitutif;
    public Parcours $parcours;
    public $newMcccToText;
    public $oldMcccToText;

    public function __construct(ElementConstitutif $elementConstitutif, Parcours $parcours, string $oldMcccToText, string $newMcccToText)
    {
        $this->elementConstitutif = $elementConstitutif;
        $this->parcours = $parcours;
        $this->oldMcccToText = $oldMcccToText;
        $this->newMcccToText = $newMcccToText;
    }

    public function getElementConstitutif(): ElementConstitutif
    {
        return $this->elementConstitutif;
    }

    public function getParcours(): Parcours
    {
        return $this->parcours;
    }

    public function getNewMcccToText()
    {
        return $this->newMcccToText;
    }

    public function getOldMcccToText()
    {
        return $this->oldMcccToText;
    }
}
