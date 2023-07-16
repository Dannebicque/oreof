<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/TypeDiplomeRegistry.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 07/03/2023 17:40
 */

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
    public function getTypeDiplome(?string $name): mixed
    {
        if ($name === null || !array_key_exists($name, $this->typesDiplomes)) {
            throw new TypeDiplomeNotFoundException();
        }

        return $this->typesDiplomes[$name];
    }

    public function getTypesDiplomes(): array
    {
        return $this->typesDiplomes;
    }
}
