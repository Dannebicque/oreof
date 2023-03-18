<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/Type/EntityWithAddType.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 21/02/2023 11:06
 */

namespace App\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityWithAddType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'help_to_add' => ''
        ]);
    }
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr'] = array_merge($options['attr'], ['data-textarea-target' => 'input']);
        $view->vars['name_field_to_add'] = $view->vars['form']->vars['name'].'_toAdd';
        $view->vars['help_to_add'] = $options['help_to_add'];
    }


    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
