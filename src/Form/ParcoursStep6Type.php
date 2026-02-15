<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/ParcoursStep7Type.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 05/03/2023 17:57
 */

namespace App\Form;

use App\Entity\Parcours;
use App\Form\Type\TextareaAutoSaveType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParcoursStep6Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('poursuitesEtudes', TextareaAutoSaveType::class, [
                'attr' => ['rows' => 20, 'maxlength' => 3000],
            ])
            ->add('debouches', TextareaAutoSaveType::class, [
                'attr' => ['rows' => 10, 'maxlength' => 3000],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Parcours::class,
            'translation_domain' => 'form'
        ]);
    }
}
