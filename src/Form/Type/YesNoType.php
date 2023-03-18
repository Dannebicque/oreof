<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/Type/YesNoType.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 24/01/2023 10:45
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class YesNoType.
 */
class YesNoType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => [
                'Oui' => true,
                'Non' => false,
            ],
            'multiple' => false,
            'expanded' => true,
        ]);
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
