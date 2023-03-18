<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/Type/TextareaWithSaveType.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/02/2023 22:12
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class YesNoType.
 */
class TextareaWithSaveType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'button_action' => null,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['button_action'] = 'data-action = '.$options['button_action'];
        $view->vars['button_disabled'] = $options['disabled'];
        $view->vars['attr'] = array_merge($options['attr'], ['data-textarea-target' => 'input']);
    }


    public function getParent(): ?string
    {
        return TextareaType::class;
    }
}
