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

        $display = null;
        $displayLong = null;

        if (is_object($f)) {
            $display = method_exists($f, 'getDisplay') ? $f->getDisplay() : null;
            $displayLong = method_exists($f, 'getDisplayLong') ? $f->getDisplayLong() : null;
        } elseif (is_array($f)) {
            $display = $f['display'] ?? null;
            $displayLong = $f['displayLong'] ?? null;
        }

        return [
            'display' => $display ?: null,
            'displayLong' => $displayLong ?: null,
        ];
    }

    public function describe(): array
    {
        return [
            'formation.display' => 'Intitulé de la formation',
            'formation.displayLong' => 'Type et intitulé de la formation',
        ];
    }

    public function previewDefaults(): array
    {
        return [
            'display' => 'Métiers du Multimédia et de l\'Internet (MMI)',
            'displayLong' => 'BUT Métiers du Multimédia et de l\'Internet (MMI)',
        ];
    }
}
