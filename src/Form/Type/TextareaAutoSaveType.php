<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/Type/TextareaAutoSaveType.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 14/03/2023 22:08
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class TextareaAutoSaveType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr'] = array_merge($options['attr'], ['data-textarea-target' => 'input']);
        $view->vars['maxLength'] = $options['attr']['maxlength'];
    }


    public function getParent(): ?string
    {
        return TrixType::class;
    }
}
