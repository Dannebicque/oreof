<?php

declare(strict_types=1);

namespace App\Workflow\Validator;

/**
 * Représente une erreur de validation bloquante.
 */
final class ValidationError
{
    /**
     * @param string $code Code unique de l'erreur (pour la traduction)
     * @param string $message Message par défaut de l'erreur
     * @param array<string, mixed> $parameters Paramètres pour la traduction
     * @param string|null $field Champ concerné par l'erreur (optionnel)
     * @param string|null $path Chemin vers l'élément en erreur (ex: parcours.formation.mention)
     */
    public function __construct(
        private readonly string  $code,
        private readonly string  $message,
        private readonly array   $parameters = [],
        private readonly ?string $field = null,
        private readonly ?string $path = null
    )
    {
    }

    /**
     * Crée une erreur simple avec code et message.
     */
    public static function create(string $code, string $message, array $parameters = []): self
    {
        return new self($code, $message, $parameters);
    }

    /**
     * Crée une erreur liée à un champ spécifique.
     */
    public static function forField(string $field, string $code, string $message, array $parameters = []): self
    {
        return new self($code, $message, $parameters, $field);
    }

    /**
     * Crée une erreur avec un chemin vers l'élément concerné.
     */
    public static function forPath(string $path, string $code, string $message, array $parameters = []): self
    {
        return new self($code, $message, $parameters, null, $path);
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

    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Retourne la clé de traduction complète.
     */
    public function getTranslationKey(): string
    {
        return 'workflow.validation.error.' . $this->code;
    }

    /**
     * Convertit l'erreur en tableau.
     *
     * @return array{code: string, message: string, parameters: array, field: ?string, path: ?string}
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'parameters' => $this->parameters,
            'field' => $this->field,
            'path' => $this->path,
        ];
    }
}
