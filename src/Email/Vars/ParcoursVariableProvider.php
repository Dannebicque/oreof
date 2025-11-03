<?php
declare(strict_types=1);

namespace App\Email\Vars;

final class ParcoursVariableProvider implements VariableProviderInterface
{
    public function getNamespace(): string
    {
        return 'parcours';
    }

    public function provide(array $context): array
    {
        $f = $context['parcours'] ?? null;

        $display = null;

        if (is_object($f)) {
            $display = method_exists($f, 'getDisplay') ? $f->getDisplay() : null;
        } elseif (is_array($f)) {
            $display = $f['display'] ?? null;
        }

        return [
            'display' => $display ?: null,
        ];
    }

    public function describe(): array
    {
        return [
            'parcours.display' => 'Intitulé du parcours',
        ];
    }

    public function previewDefaults(): array
    {
        return [
            'display' => 'Création numérique',
        ];
    }
}
