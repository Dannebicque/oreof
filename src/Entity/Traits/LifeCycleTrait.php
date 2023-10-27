<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Entity/Traits/LifeCycleTrait.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/02/2023 22:43
 */

namespace App\Entity\Traits;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trait LifeCycleTrait.
 */
trait LifeCycleTrait
{
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $created = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $updated = null;

    public function getCreated(): ?DateTimeInterface
    {
        return $this->created ?? new DateTimeImmutable('now');
    }

    public function setCreated(?DateTimeInterface $created): void
    {
        $this->created = $created;
    }

    public function getUpdated(): ?DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(?DateTimeInterface $updated): void
    {
        $this->updated = $updated;
    }

    public function setUpdatedValue(): void
    {
        $this->updated = new DateTimeImmutable('now');
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setUpdatedEntity(): void
    {
        $this->updated = new DateTimeImmutable('now');
    }

    #[ORM\PrePersist]
    public function setCreatedValue(): void
    {
        $this->created = new DateTimeImmutable('now');
    }

    // Fix pour la serialization
    public function getUpdatedValue(){
        return $this->updated;
    }
    // Fix pour la serialization
    public function getUpdatedEntity(){
        return new DateTimeImmutable('now');
    }
    // Fix pour la serialization
    public function getCreatedValue(){
        return $this->created;
    }

}
