<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Workflow/Form/MetaFormTypeResolver.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 12/02/2026 16:30
 */

namespace App\Workflow\Form;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class MetaFormTypeResolver
{
    private const array MAP = [
        'text' => TextType::class,
        'file' => FileType::class,
        'textarea' => TextareaType::class,
        'checkbox' => CheckboxType::class,
        'date' => DateType::class,
        'choice' => ChoiceType::class,
    ];

    /** @return class-string */
    public function resolve(string $type): string
    {
        if (!isset(self::MAP[$type])) {
            throw new \InvalidArgumentException(sprintf('Type de champ "%s" non supporté', $type));
        }
        return self::MAP[$type];
    }
}
