<?php

declare(strict_types=1);

namespace App\Workflow\Validator;

use App\Entity\DpeParcours;
use App\Entity\Parcours;
use App\TypeDiplome\TypeDiplomeResolver;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Classe de base abstraite pour les validators d'étapes.
 *
 * Fournit les méthodes utilitaires communes et l'injection des services nécessaires.
 */
abstract class AbstractStepValidator implements StepValidatorInterface
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly TypeDiplomeResolver    $typeDiplomeResolver
    )
    {
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $stepCode): bool
    {
        return $this->getStepCode() === $stepCode;
    }

    /**
     * Vérifie si le parcours est de type BUT.
     */
    protected function isBut(DpeParcours $dpeParcours): bool
    {
        return $this->getTypeDiplomeCode($dpeParcours) === 'BUT';
    }

    /**
     * Récupère le code du type de diplôme.
     *
     * @return string|null Code du type (BUT, LP, Master, etc.)
     */
    protected function getTypeDiplomeCode(DpeParcours $dpeParcours): ?string
    {
        return $dpeParcours->getFormation()?->getTypeDiplome()?->getLibelleCourt();
    }

    /**
     * Vérifie si le parcours est une Licence Professionnelle.
     */
    protected function isLicencePro(DpeParcours $dpeParcours): bool
    {
        return $this->getTypeDiplomeCode($dpeParcours) === 'LP';
    }

    /**
     * Vérifie si le parcours est un Master.
     */
    protected function isMaster(DpeParcours $dpeParcours): bool
    {
        return in_array($this->getTypeDiplomeCode($dpeParcours), ['Master', 'M'], true);
    }

    /**
     * Vérifie si le parcours est un DUT (obsolète mais peut exister en historique).
     */
    protected function isDut(DpeParcours $dpeParcours): bool
    {
        return $this->getTypeDiplomeCode($dpeParcours) === 'DUT';
    }

    /**
     * Vérifie si le parcours est une Licence.
     */
    protected function isLicence(DpeParcours $dpeParcours): bool
    {
        return in_array($this->getTypeDiplomeCode($dpeParcours), ['Licence', 'L'], true);
    }

    /**
     * Crée un avertissement.
     */
    protected function createWarning(string $code, string $message, array $parameters = []): ValidationWarning
    {
        return ValidationWarning::create($code, $message, $parameters);
    }

    /**
     * Crée un avertissement avec suggestion.
     */
    protected function createWarningWithSuggestion(string $code, string $message, string $suggestion, array $parameters = []): ValidationWarning
    {
        return ValidationWarning::withSuggestion($code, $message, $suggestion, $parameters);
    }

    /**
     * Vérifie si un tableau est vide ou null.
     */
    protected function isEmptyArray(?array $value): bool
    {
        return $value === null || count($value) === 0;
    }

    /**
     * Valide les données communes requises pour toutes les étapes.
     *
     * @return array{errors: ValidationError[], warnings: ValidationWarning[]}
     */
    protected function validateCommonRequirements(DpeParcours $dpeParcours): array
    {
        $errors = [];
        $warnings = [];

        $parcours = $this->getParcours($dpeParcours);

        if ($parcours === null) {
            $errors[] = $this->createError(
                'parcours.missing',
                'Le parcours associé est manquant.'
            );
            return ['errors' => $errors, 'warnings' => $warnings];
        }

        $formation = $dpeParcours->getFormation();

        if ($formation === null) {
            $errors[] = $this->createError(
                'formation.missing',
                'La formation associée est manquante.'
            );
            return ['errors' => $errors, 'warnings' => $warnings];
        }

        // Vérification du type de diplôme
        if ($formation->getTypeDiplome() === null) {
            $errors[] = $this->createError(
                'type_diplome.missing',
                'Le type de diplôme n\'est pas défini.'
            );
        }

        // Vérification du libellé du parcours
        if ($this->isEmpty($parcours->getLibelle())) {
            $errors[] = $this->createFieldError(
                'libelle',
                'parcours.libelle.empty',
                'Le libellé du parcours est obligatoire.'
            );
        }

        // Vérification de la composante porteuse
        if ($formation->getComposantePorteuse() === null) {
            $errors[] = $this->createError(
                'composante.missing',
                'La composante porteuse de la formation n\'est pas définie.'
            );
        }

        return ['errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Récupère le parcours associé au DpeParcours.
     */
    protected function getParcours(DpeParcours $dpeParcours): ?Parcours
    {
        return $dpeParcours->getParcours();
    }

    /**
     * Crée une erreur de validation.
     */
    protected function createError(string $code, string $message, array $parameters = []): ValidationError
    {
        return ValidationError::create($code, $message, $parameters);
    }

    /**
     * Vérifie si une chaîne est vide ou null.
     */
    protected function isEmpty(?string $value): bool
    {
        return $value === null || trim($value) === '';
    }

    /**
     * Crée une erreur liée à un champ.
     */
    protected function createFieldError(string $field, string $code, string $message, array $parameters = []): ValidationError
    {
        return ValidationError::forField($field, $code, $message, $parameters);
    }

    /**
     * Calcule le taux de remplissage du parcours.
     *
     * @return float Pourcentage de remplissage (0-100)
     */
    protected function getRemplissagePercentage(DpeParcours $dpeParcours): float
    {
        $parcours = $this->getParcours($dpeParcours);

        if ($parcours === null) {
            return 0.0;
        }

        $remplissage = $parcours->getRemplissage();
        $total = $remplissage->total;

        if ($total === 0) {
            return 0.0;
        }

        return ($remplissage->score / $total) * 100;
    }
}
