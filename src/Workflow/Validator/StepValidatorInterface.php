<?php

declare(strict_types=1);

namespace App\Workflow\Validator;

use App\Entity\DpeParcours;

/**
 * Interface pour les validators d'étapes du workflow DpeParcours.
 *
 * Chaque validator est responsable de vérifier les prérequis métier
 * pour une étape spécifique du workflow de validation.
 */
interface StepValidatorInterface
{
    /**
     * Valide les données du DpeParcours pour l'étape concernée.
     *
     * @param DpeParcours $dpeParcours L'entité à valider
     * @return ValidationResult Le résultat de la validation contenant erreurs et avertissements
     */
    public function validate(DpeParcours $dpeParcours): ValidationResult;

    /**
     * Retourne le code de l'étape gérée par ce validator.
     *
     * Ce code correspond au nom de la place dans le workflow YAML
     * (ex: 'soumis_parcours', 'soumis_conseil', 'soumis_cfvu').
     *
     * @return string Le code de l'étape
     */
    public function getStepCode(): string;

    /**
     * Indique si ce validator supporte l'étape donnée.
     *
     * @param string $stepCode Le code de l'étape à vérifier
     * @return bool True si ce validator gère cette étape
     */
    public function supports(string $stepCode): bool;
}
