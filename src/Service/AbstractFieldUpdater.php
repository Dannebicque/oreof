<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Service/AbstractFieldUpdater.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 09/02/2026 11:38
 */

namespace App\Service;

abstract class AbstractFieldUpdater
{
    protected function toStringOrNull(mixed $v): ?string
    {
        $s = $this->toString($v);
        return trim($s) === '' ? null : $s;
    }

    protected function toString(mixed $v): string
    {
        return \is_string($v) ? $v : (string)($v ?? '');
    }

    protected function toFloatOrNull(mixed $v): ?float
    {
        if ($v === null) {
            return null;
        }
        if (\is_string($v) && trim($v) === '') {
            return null;
        }
        return (float)$v;
    }

    protected function toBoolOrNull(mixed $v): ?bool
    {
        if ($v === null) {
            return null;
        }
        if (\is_string($v) && trim($v) === '') {
            return null;
        }

        $true = [true, 1, '1', 'on', 'true', 'yes', 'oui'];
        $false = [false, 0, '0', 'off', 'false', 'no', 'non'];

        if (\in_array($v, $true, true)) {
            return true;
        }
        if (\in_array($v, $false, true)) {
            return false;
        }

        return null;
    }

    protected function toEntity(object $repo, mixed $v): ?object
    {
        if ($v === null) {
            return null;
        }
        if (\is_string($v) && trim($v) === '') {
            return null;
        }

        $id = (int)$v;
        if ($id <= 0) {
            return null;
        }

        // repo Doctrine classique : find($id)
        return $repo->find($id);
    }

    protected function toEnumOrNull(string $enumClass, mixed $v): ?\BackedEnum
    {
        if ($v === null) {
            return null;
        }
        if (\is_string($v) && trim($v) === '') {
            return null;
        }

        if (!\is_subclass_of($enumClass, \BackedEnum::class)) {
            throw new \InvalidArgumentException("$enumClass is not a BackedEnum");
        }

        /** @var class-string<\BackedEnum> $enumClass */
        return $enumClass::tryFrom((string)$v);
    }

    /**
     * @return array<\BackedEnum>
     */
    protected function toEnumArray(string $enumClass, mixed $v): array
    {
        $values = $this->toArray($v); // JSON array / array / csv

        if (!\is_subclass_of($enumClass, \BackedEnum::class)) {
            throw new \InvalidArgumentException("$enumClass is not a BackedEnum");
        }

        /** @var class-string<\BackedEnum> $enumClass */
        $out = [];
        foreach ($values as $raw) {
            if ($raw === null || $raw === '') {
                continue;
            }
            $e = $enumClass::tryFrom((string)$raw);
            if ($e !== null) {
                $out[] = $e;
            }
        }

        // Unicité
        $uniq = [];
        foreach ($out as $e) {
            $uniq[$e->value] = $e;
        }

        return array_values($uniq);
    }

    protected function toArray(mixed $v): array
    {
        if (\is_array($v)) {
            return array_values(array_filter($v, fn($x) => $x !== null && $x !== ''));
        }
        if (!\is_string($v)) {
            return [];
        }

        $s = trim($v);
        if ($s === '') {
            return [];
        }

        // accepte JSON
        if ($s[0] === '[') {
            $decoded = json_decode($s, true);
            return \is_array($decoded) ? array_values($decoded) : [];
        }

        // accepte CSV "A,B,C"
        return array_values(array_filter(array_map('trim', explode(',', $s))));
    }

    protected function toIntOrNull(mixed $v): ?int
    {
        if ($v === null) {
            return null;
        }
        if (\is_string($v) && trim($v) === '') {
            return null;
        }
        return (int)$v;
    }

    /**
     * @param iterable $currentCollection La collection actuelle de l'entité (ex: $formation->getLocalisations())
     * @param object $repo Le repository pour charger les entités
     * @param mixed $v Les IDs reçus (array, JSON ou CSV)
     * @param callable $adder La fonction addXxx de l'entité
     * @param callable $remover La fonction removeXxx de l'entité
     */
    protected function syncCollection(
        iterable $currentCollection,
        object   $repo,
        mixed    $v,
        callable $adder,
        callable $remover
    ): void
    {
        $newIds = array_map('intval', $this->toArray($v));
        $currentIds = [];

        // 1. Identifier les entités à supprimer
        foreach ($currentCollection as $entity) {
            $currentId = $entity->getId();
            $currentIds[] = $currentId;

            if (!in_array($currentId, $newIds, true)) {
                $remover($entity);
            }
        }

        // 2. Identifier les entités à ajouter
        foreach ($newIds as $id) {
            if (!in_array($id, $currentIds, true)) {
                $entity = $repo->find($id);
                if ($entity !== null) {
                    $adder($entity);
                }
            }
        }
    }

}
