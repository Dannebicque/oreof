<?php

namespace App\Service\Validation;

use App\Entity\Semestre;
use App\Entity\Ue;
use App\Entity\ValidationIssue;
use App\Enums\ValidationStatusEnum;
use App\Repository\ValidationIssueRepository;
use App\Service\Validation\Dto\ValidationIssueDto;
use App\Service\Validation\Dto\ValidationResult;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockInterface;

/**
 * Applique un ValidationResult en BDD :
 * - purge et recrée les ValidationIssue du semestre
 * - met à jour les flags validation (Semestre + UE + EC)
 * - remet validationDirty à false + validationUpdatedAt
 */
final readonly class ValidationResultApplier
{
    public function __construct(
        private EntityManagerInterface    $em,
        private ValidationIssueRepository $issueRepository,
        private ClockInterface            $clock,
    )
    {
    }

    public function apply(
        Semestre         $semestre,
        ValidationResult $result,
        string           $typeDiplome,
        bool             $flush = true
    ): void
    {
        $now = $this->clock->now();

        // 1) Purge des issues existantes pour ce semestre
        $this->issueRepository->deleteBySemestre($semestre);

        // 2) Insertion des nouvelles issues
        foreach ($result->getIssues() as $issueDto) {
            $this->em->persist($this->toEntity($semestre, $issueDto, $typeDiplome, $now));
        }

        // 3) Appliquer statuts sur Semestre
        $semestre
            ->setValidationStatus($result->getSemestreStatus())
            ->setValidationDirty(false)
            ->setValidationUpdatedAt($now);

        // 4) Appliquer statuts sur UE / EC (avec fallback)
        $ueStatuses = $result->getUeStatuses(); // array<int|string, ValidationStatus>
        $ecStatuses = $result->getEcStatuses(); // array<int|string, ValidationStatus>

        foreach ($semestre->getUes() as $ue) {
            $ueId = $ue->getId();

            $ueStatus = $ueId !== null && isset($ueStatuses[$ueId])
                ? $ueStatuses[$ueId]
                : $this->fallbackStatusForUe($ue, $ecStatuses);

            $ue
                ->setValidationStatus($ueStatus)
                ->setValidationDirty(false)
                ->setValidationUpdatedAt($now);

            foreach ($ue->getElementConstitutifs() as $ec) {
                $ecId = $ec->getId();

                $ecStatus = $ecId !== null && isset($ecStatuses[$ecId])
                    ? $ecStatuses[$ecId]
                    : ValidationStatusEnum::VALID;

                $ec
                    ->setValidationStatus($ecStatus)
                    ->setValidationDirty(false)
                    ->setValidationUpdatedAt($now);
            }
        }

        if ($flush) {
            $this->em->flush();
        }
    }

    private function toEntity(
        Semestre           $semestre,
        ValidationIssueDto $dto,
        string             $typeDiplome,
        \DateTimeImmutable $now
    ): ValidationIssue
    {
        $issue = new ValidationIssue();
        $issue->setSemestre($semestre);
        $issue->setScopeType($dto->scopeType);     // ex: "ec"
        $issue->setScopeId($dto->scopeId); // stocke en string pour couvrir int/uuid
        $issue->setRuleCode($dto->ruleCode);       // ex: "MCCC_MISSING"
        $issue->setSeverity($dto->severity);       // ex: "error|warning|info"
        $issue->setMessage($dto->message);
        $issue->setPayload($dto->payload);
        $issue->setTypeDiplome($typeDiplome);
        $issue->setCreatedAt($now);

        return $issue;
    }

    /**
     * Fallback UE :
     * - si un de ses EC est INVALID => UE INVALID
     * - si un de ses EC est INCOMPLETE => UE INCOMPLETE
     * - sinon VALID
     *
     * (Tu peux durcir/adapter selon tes règles UE)
     *
     * @param array<int|string, ValidationStatusEnum> $ecStatuses
     */
    private function fallbackStatusForUe(Ue $ue, array $ecStatuses): ValidationStatusEnum
    {
        $hasIncomplete = false;

        foreach ($ue->getElementConstitutifs() as $ec) {
            $id = $ec->getId();
            if ($id === null || !isset($ecStatuses[$id])) {
                continue;
            }

            $st = $ecStatuses[$id];
            if ($st === ValidationStatusEnum::INVALID) {
                return ValidationStatusEnum::INVALID;
            }
            if ($st === ValidationStatusEnum::INCOMPLETE) {
                $hasIncomplete = true;
            }
        }

        return $hasIncomplete ? ValidationStatusEnum::INCOMPLETE : ValidationStatusEnum::VALID;
    }
}
