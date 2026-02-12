<?php

declare(strict_types=1);

namespace App\Workflow\Service;

use App\Entity\DpeParcours;
use App\Workflow\Validator\StepValidatorInterface;
use App\Workflow\Validator\ValidationResult;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * Service centralisé de validation pour le workflow DpeParcours.
 *
 * Gère l'ensemble des validators d'étapes et fournit une API unifiée
 * pour valider les transitions du workflow.
 */
final class ValidationService
{
    /**
     * @var array<string, StepValidatorInterface>
     */
    private array $validatorsMap = [];

    /**
     * @param iterable<StepValidatorInterface> $validators Validators injectés via le tag workflow.validator
     */
    public function __construct(
        #[AutowireIterator('workflow.validator')]
        private readonly iterable $validators
    )
    {
        $this->buildValidatorsMap();
    }

    /**
     * Construit la map des validators indexée par code d'étape.
     */
    private function buildValidatorsMap(): void
    {
        foreach ($this->validators as $validator) {
            $this->validatorsMap[$validator->getStepCode()] = $validator;
        }
    }

    /**
     * Vérifie si une transition vers l'étape cible est possible.
     *
     * Cette méthode est destinée à être appelée depuis les guards du workflow YAML.
     *
     * @param DpeParcours $dpeParcours L'entité à valider
     * @param string $targetStepCode Le code de l'étape cible
     * @return bool True si la transition est autorisée
     */
    public function canTransition(DpeParcours $dpeParcours, string $targetStepCode): bool
    {
        $result = $this->validateForStep($dpeParcours, $targetStepCode);
        return $result->isValid();
    }

    /**
     * Valide un DpeParcours pour une étape donnée.
     *
     * @param DpeParcours $dpeParcours L'entité à valider
     * @param string $stepCode Le code de l'étape cible
     * @return ValidationResult Le résultat de validation
     */
    public function validateForStep(DpeParcours $dpeParcours, string $stepCode): ValidationResult
    {
        $validator = $this->getValidatorForStep($stepCode);

        if ($validator === null) {
            // Si pas de validator pour cette étape, la validation passe par défaut
            return ValidationResult::success();
        }

        return $validator->validate($dpeParcours);
    }

    /**
     * Retourne le validator pour une étape donnée.
     *
     * @param string $stepCode Le code de l'étape
     * @return StepValidatorInterface|null Le validator ou null si non trouvé
     */
    public function getValidatorForStep(string $stepCode): ?StepValidatorInterface
    {
        return $this->validatorsMap[$stepCode] ?? null;
    }

    /**
     * Vérifie si un validator existe pour une étape.
     *
     * @param string $stepCode Le code de l'étape
     * @return bool True si un validator existe
     */
    public function hasValidatorForStep(string $stepCode): bool
    {
        return isset($this->validatorsMap[$stepCode]);
    }

    /**
     * Retourne la liste des étapes ayant un validator.
     *
     * @return array<string>
     */
    public function getValidatedSteps(): array
    {
        return array_keys($this->validatorsMap);
    }

    /**
     * Valide plusieurs étapes et retourne tous les résultats.
     *
     * Utile pour afficher un récapitulatif complet des validations.
     *
     * @param DpeParcours $dpeParcours L'entité à valider
     * @param array<string> $stepCodes Les codes des étapes à valider
     * @return array<string, ValidationResult> Map des résultats par étape
     */
    public function validateMultipleSteps(DpeParcours $dpeParcours, array $stepCodes): array
    {
        $results = [];

        foreach ($stepCodes as $stepCode) {
            $results[$stepCode] = $this->validateForStep($dpeParcours, $stepCode);
        }

        return $results;
    }

    /**
     * Valide toutes les étapes configurées et retourne un résultat global.
     *
     * @param DpeParcours $dpeParcours L'entité à valider
     * @return ValidationResult Résultat fusionné de toutes les validations
     */
    public function validateAll(DpeParcours $dpeParcours): ValidationResult
    {
        $globalResult = ValidationResult::success();

        foreach ($this->validatorsMap as $validator) {
            $result = $validator->validate($dpeParcours);
            $globalResult = $globalResult->merge($result);
        }

        return $globalResult;
    }
}
