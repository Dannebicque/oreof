<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/FicheMatiereStep3Type.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 05/03/2023 16:19
 */

namespace App\Form;

use App\Entity\ElementConstitutif;
use App\Form\Type\TextareaAutoSaveType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FicheMatiereStep3Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('objectifs', TextareaAutoSaveType::class, [
                'attr' => ['data-action' => 'change->ec--step3#saveObjectifs', 'maxlength' => 3000, 'rows' => 20, 'class' => 'tinyMce'],
                'help' => '-'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElementConstitutif::class,
            'translation_domain' => 'form'
        ]);
    }
}
