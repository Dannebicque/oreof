<?php

namespace App\Entity;

interface CentreRestrictedInterface
{
    /**
     * Retourne les centres autorisés pour cette entité
     */
    public function getCentresShow(): array;
}
