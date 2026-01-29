<?php

namespace App\Entity;

use App\Repository\ValidationIssueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ValidationIssueRepository::class)]
class ValidationIssue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'validationIssues')]
    private ?Semestre $semestre = null;

    #[ORM\Column()]
    private string $scopeType;

    #[ORM\Column]
    private ?int $scopeId = null;

    #[ORM\Column(length: 255)]
    private ?string $ruleCode = null;

    #[ORM\Column(length: 15)]
    private ?string $severity = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $message = null;

    #[ORM\Column(nullable: true)]
    private ?array $payload = null;

    #[ORM\Column(length: 255)]
    private ?string $typeDiplome = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSemestre(): ?Semestre
    {
        return $this->semestre;
    }

    public function setSemestre(?Semestre $semestre): static
    {
        $this->semestre = $semestre;

        return $this;
    }

    public function getScopeType(): string
    {
        return $this->scopeType;
    }

    public function setScopeType(string $scopeType): static
    {
        $this->scopeType = $scopeType;

        return $this;
    }

    public function getScopeId(): ?int
    {
        return $this->scopeId;
    }

    public function setScopeId(int $scopeId): static
    {
        $this->scopeId = $scopeId;

        return $this;
    }

    public function getRuleCode(): ?string
    {
        return $this->ruleCode;
    }

    public function setRuleCode(string $ruleCode): static
    {
        $this->ruleCode = $ruleCode;

        return $this;
    }

    public function getSeverity(): ?string
    {
        return $this->severity;
    }

    public function setSeverity(string $severity): static
    {
        $this->severity = $severity;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function setPayload(?array $payload): static
    {
        $this->payload = $payload;

        return $this;
    }

    public function getTypeDiplome(): ?string
    {
        return $this->typeDiplome;
    }

    public function setTypeDiplome(string $typeDiplome): static
    {
        $this->typeDiplome = $typeDiplome;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
