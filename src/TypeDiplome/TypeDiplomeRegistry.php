<?php

namespace App\TypeDiplome;

use App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException;
use App\TypeDiplome\Source\TypeDiplomeInterface;

class TypeDiplomeRegistry
{
    final public const TAG_TYPE_DIPLOME = 'da.type.diplome';

    private array $typesDiplomes = [];

    public function getChoices(): array
    {
        $t = [];
        foreach ($this->typesDiplomes as $key => $value) {
            $t[$value::SOURCE] = $key;
        }

        return $t;
    }

    public function registerTypeDiplome(string $name, TypeDiplomeInterface $abstractTypeDiplome): void
    {
        $this->typesDiplomes[$abstractTypeDiplome::class] = $abstractTypeDiplome;
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    public function getTypeDiplome(string $name): mixed
    {
        if (!array_key_exists($name, $this->typesDiplomes)) {
            throw new TypeDiplomeNotFoundException();
        }

        return $this->typesDiplomes[$name];
    }

    public function getTypesDiplomes(): array
    {
        return $this->typesDiplomes;
    }
}
