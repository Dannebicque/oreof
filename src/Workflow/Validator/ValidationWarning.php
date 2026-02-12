<?php

declare(strict_types=1);

namespace App\Workflow\Validator;

/**
 * Représente un avertissement de validation non-bloquant.
 */
final class ValidationWarning
{
    /**
     * @param string $code Code unique de l'avertissement (pour la traduction)
     * @param string $message Message par défaut de l'avertissement
     * @param array<string, mixed> $parameters Paramètres pour la traduction
     * @param string|null $field Champ concerné par l'avertissement (optionnel)
     * @param string|null $suggestion Suggestion pour résoudre l'avertissement
     */
    public function __construct(
        private readonly string  $code,
        private readonly string  $message,
        private readonly array   $parameters = [],
        private readonly ?string $field = null,
        private readonly ?string $suggestion = null
    )
    {
    }

    /**
     * Crée un avertissement simple.
     */
    public static function create(string $code, string $message, array $parameters = []): self
    {
        return new self($code, $message, $parameters);
    }

    /**
     * Crée un avertissement avec une suggestion.
     */
    public static function withSuggestion(string $code, string $message, string $suggestion, array $parameters = []): self
    {
        return new self($code, $message, $parameters, null, $suggestion);
    }

    /**
     * Crée un avertissement lié à un champ.
     */
    public static function forField(string $field, string $code, string $message, array $parameters = []): self
    {
        return new self($code, $message, $parameters, $field);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function getSuggestion(): ?string
    {
        return $this->suggestion;
    }

    /**
     * Retourne la clé de traduction complète.
     */
    public function getTranslationKey(): string
    {
        return 'workflow.validation.warning.' . $this->code;
    }

    /**
     * Convertit l'avertissement en tableau.
     *
     * @return array{code: string, message: string, parameters: array, field: ?string, suggestion: ?string}
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'parameters' => $this->parameters,
            'field' => $this->field,
            'suggestion' => $this->suggestion,
        ];
    }
}
