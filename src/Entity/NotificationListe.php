<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Entity/NotificationListe.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 26/01/2023 21:04
 */

namespace App\Entity;

use App\Repository\NotificationListeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationListeRepository::class)]
class NotificationListe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $codeNotification = null;

    #[ORM\Column]
    private ?bool $toSes = false;

    #[ORM\Column]
    private ?bool $toVp = false;

    #[ORM\Column]
    private ?bool $toRespDpe = false;

    #[ORM\Column]
    private ?bool $toRespFormation = false;

    #[ORM\Column]
    private ?bool $toRespEc = false;

    #[ORM\Column]
    private ?bool $isComposante = null;

    #[ORM\Column]
    private ?bool $isCentral = null;

    #[ORM\Column(length: 150)]
    private ?string $libelle = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeNotification(): ?string
    {
        return $this->codeNotification;
    }

    public function setCodeNotification(string $codeNotification): self
    {
        $this->codeNotification = $codeNotification;

        return $this;
    }

    public function isToSes(): ?bool
    {
        return $this->toSes;
    }

    public function setToSes(bool $toSes): self
    {
        $this->toSes = $toSes;

        return $this;
    }

    public function isToVp(): ?bool
    {
        return $this->toVp;
    }

    public function setToVp(bool $toVp): self
    {
        $this->toVp = $toVp;

        return $this;
    }

    public function isToRespDpe(): ?bool
    {
        return $this->toRespDpe;
    }

    public function setToRespDpe(bool $toRespDpe): self
    {
        $this->toRespDpe = $toRespDpe;

        return $this;
    }

    public function isToRespFormation(): ?bool
    {
        return $this->toRespFormation;
    }

    public function setToRespFormation(bool $toRespFormation): self
    {
        $this->toRespFormation = $toRespFormation;

        return $this;
    }

    public function isToRespEc(): ?bool
    {
        return $this->toRespEc;
    }

    public function setToRespEc(bool $toRespEc): self
    {
        $this->toRespEc = $toRespEc;

        return $this;
    }

    public function isComposante(): ?bool
    {
        return $this->isComposante;
    }

    public function setIsComposante(bool $isComposante): self
    {
        $this->isComposante = $isComposante;

        return $this;
    }

    public function isCentral(): ?bool
    {
        return $this->isCentral;
    }

    public function setIsCentral(bool $isCentral): self
    {
        $this->isCentral = $isCentral;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }
}
