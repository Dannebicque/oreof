<?php

namespace App\Entity;

use App\Repository\ParcoursVersioningRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParcoursVersioningRepository::class)]
class ParcoursVersioning
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $json_data = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJsonData(): ?string
    {
        return $this->json_data;
    }

    public function setJsonData(string $json_data): static
    {
        $this->json_data = $json_data;

        return $this;
    }
}
