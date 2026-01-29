<?php

namespace App\Service\Validation\Dto;

use App\Enums\ValidationStatusEnum;

final class ValidationResult
{
    private ValidationStatusEnum $semestreStatus = ValidationStatusEnum::VALID;

    /** @var array<int|string, ValidationStatusEnum> */
    private array $ueStatuses = [];

    /** @var array<int|string, ValidationStatusEnum> */
    private array $ecStatuses = [];

    /** @var ValidationIssueDto[] */
    private array $issues = [];

    // -----------------------
    // STATUS GLOBAL
    // -----------------------

    public function getSemestreStatus(): ValidationStatusEnum
    {
        return $this->semestreStatus;
    }

    public function setSemestreStatus(ValidationStatusEnum $status): self
    {
        $this->semestreStatus = $status;
        return $this;
    }

    // -----------------------
    // UE STATUS
    // -----------------------

    public function setUeStatus(int|string $ueId, ValidationStatusEnum $status): self
    {
        $this->ueStatuses[$ueId] = $status;
        return $this;
    }

    /** @return array<int|string, ValidationStatusEnum> */
    public function getUeStatuses(): array
    {
        return $this->ueStatuses;
    }

    // -----------------------
    // EC STATUS
    // -----------------------

    public function setEcStatus(int|string $ecId, ValidationStatusEnum $status): self
    {
        $this->ecStatuses[$ecId] = $status;
        return $this;
    }

    /** @return array<int|string, ValidationStatusEnum> */
    public function getEcStatuses(): array
    {
        return $this->ecStatuses;
    }

    // -----------------------
    // ISSUES
    // -----------------------

    public function addIssue(ValidationIssueDto $issue): self
    {
        $this->issues[] = $issue;
        return $this;
    }

    /** @return ValidationIssueDto[] */
    public function getIssues(): array
    {
        return $this->issues;
    }

    // -----------------------
    // HELPERS UTILES
    // -----------------------

    public function hasErrors(): bool
    {
        foreach ($this->issues as $issue) {
            if ($issue->severity === 'error') {
                return true;
            }
        }
        return false;
    }

    /**
     * Auto-calcule les statuts global si tu ne les définis pas explicitement.
     * Tu peux appeler ça à la fin du validateur.
     */
    public function autoComputeGlobalStatus(): void
    {
        if ($this->hasErrors()) {
            $this->semestreStatus = ValidationStatusEnum::INVALID;
        }
    }
}
