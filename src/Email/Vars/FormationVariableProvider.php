<?php
declare(strict_types=1);

namespace App\Email\Vars;

final class FormationVariableProvider implements VariableProviderInterface
{
    public function getNamespace(): string
    {
        return 'formation';
    }

    public function provide(array $context): array
    {
        $f = $context['formation'] ?? null;

        $name = null;
        $code = null;

        if (is_object($f)) {
            $name = method_exists($f, 'getName') ? $f->getName() : null;
            $code = method_exists($f, 'getCode') ? $f->getCode() : null;
        } elseif (is_array($f)) {
            $name = $f['name'] ?? null;
            $code = $f['code'] ?? null;
        }

        return [
            'name' => $name ?: null,
            'code' => $code ?: null,
        ];
    }

    public function describe(): array
    {
        return [
            'formation.name' => 'Intitulé de la formation',
            'formation.code' => 'Code de la formation',
        ];
    }

    public function previewDefaults(): array
    {
        return [
            'name' => 'BUT MMI',
            'code' => 'MMI',
        ];
    }
}
