<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Entity/TypeUe.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 11/02/2023 07:51
 */

namespace App\Entity;

use App\Repository\TypeUeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeUeRepository::class)]
class TypeUe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $libelle = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $typeDiplome = [];

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

    public function getTypeDiplome(): array
    {
        return $this->typeDiplome ?? [];
    }

    public function setTypeDiplome(?array $typeDiplome): self
    {
        $this->typeDiplome = $typeDiplome;

        return $this;
    }
}
