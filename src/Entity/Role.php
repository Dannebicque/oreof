<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Entity/Role.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 15/02/2023 17:44
 */

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $libelle = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $droits = [];

    #[ORM\Column(length: 100)]
    private ?string $code_role = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $porte = null;

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

    public function getDroits(): array
    {
        return $this->droits ?? [];
    }

    public function setDroits(?array $droits): self
    {
        $this->droits = $droits;

        return $this;
    }

    public function getCodeRole(): ?string
    {
        return $this->code_role;
    }

    public function setCodeRole(string $code_role): self
    {
        $this->code_role = $code_role;

        return $this;
    }

    public function getPorte(): ?string
    {
        return $this->porte;
    }

    public function setPorte(?string $porte): self
    {
        $this->porte = $porte;

        return $this;
    }

    public function hasDroit(string $role): bool
    {
        return in_array($role, $this->getDroits(), true);
    }
}
