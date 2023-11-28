<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Entity/AnneeUniversitaire.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/02/2023 22:43
 */

namespace App\Entity;

use App\Repository\AnneeUniversitaireRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: AnneeUniversitaireRepository::class)]
class AnneeUniversitaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $libelle = null;

    #[ORM\Column]
    private ?int $annee = null;

    #[ORM\Column]
    private ?bool $defaut = null;

    #[Ignore]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dateOuvertureDpe = null;

    #[Ignore]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dateClotureDpe = null;

    #[Ignore]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dateTransmissionSes = null;

    #[Ignore]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dateCfvu = null;

    #[Ignore]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $datePublication = null;

    #[ORM\Column(length: 1)]
    private ?string $codeApogee = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(int $annee): self
    {
        $this->annee = $annee;

        return $this;
    }

    public function isDefaut(): ?bool
    {
        return $this->defaut;
    }

    public function setDefaut(bool $defaut): self
    {
        $this->defaut = $defaut;

        return $this;
    }

    public function getDateTransmissionSes(): ?DateTimeInterface
    {
        return $this->dateTransmissionSes;
    }

    public function setDateTransmissionSes(?DateTimeInterface $dateTransmissionSes): self
    {
        $this->dateTransmissionSes = $dateTransmissionSes;

        return $this;
    }

    public function getDateCfvu(): ?DateTimeInterface
    {
        return $this->dateCfvu;
    }

    public function setDateCfvu(?DateTimeInterface $dateCfvu): self
    {
        $this->dateCfvu = $dateCfvu;

        return $this;
    }

    public function getDateOuvertureDpe(): ?DateTimeInterface
    {
        return $this->dateOuvertureDpe;
    }

    public function setDateOuvertureDpe(?DateTimeInterface $dateOuvertureDpe): self
    {
        $this->dateOuvertureDpe = $dateOuvertureDpe;

        return $this;
    }

    public function getDateClotureDpe(): ?DateTimeInterface
    {
        return $this->dateClotureDpe;
    }

    public function setDateClotureDpe(?DateTimeInterface $dateClotureDpe): self
    {
        $this->dateClotureDpe = $dateClotureDpe;

        return $this;
    }

    public function getDatePublication(): ?DateTimeInterface
    {
        return $this->datePublication;
    }

    public function setDatePublication(?DateTimeInterface $datePublication): self
    {
        $this->datePublication = $datePublication;

        return $this;
    }

    public function getCodeApogee(): ?string
    {
        return $this->codeApogee;
    }

    public function setCodeApogee(string $codeApogee): static
    {
        $this->codeApogee = $codeApogee;

        return $this;
    }
}
