<?php

namespace App\Workflow\Form;

final class MetaFormOptionsFilter
{
    private const ALLOWED = [
        'attr', 'row_attr',
        'widget', 'placeholder',
        'choices', 'expanded', 'multiple',
        'input', 'years', 'format',
    ];

    public function filter(array $options): array
    {
        $out = [];
        foreach ($options as $k => $v) {
            if (\in_array($k, self::ALLOWED, true)) {
                $out[$k] = $v;
            }
        }
        return $out;
    }
}
