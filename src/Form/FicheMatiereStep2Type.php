<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/FicheMatiereStep2Type.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 05/03/2023 16:15
 */

namespace App\Form;

use App\Entity\ElementConstitutif;
use App\Entity\Langue;
use App\Entity\NatureUeEc;
use App\Form\Type\TextareaAutoSaveType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FicheMatiereStep2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('description', TextareaAutoSaveType::class, [
                'attr' => ['data-action' => 'change->ec--step2#saveDescription', 'maxlength' => 3000, 'rows' => 20, 'class' => 'tinyMce'],
                'help' => '-'
            ])
            ->add('langueDispense', EntityType::class, [
                'attr' => ['data-action' => 'change->ec--step2#changeLangue', 'data-ec--step2-type-param' => 'langueDispense' ],
                'class' => Langue::class,
                'choice_label' => 'libelle',
                'expanded' => true,
                'multiple' => true,
                'required' => true,
                'help' => '-'
            ])
            ->add('langueSupport', EntityType::class, [
                'attr' => ['data-action' => 'change->ec--step2#changeLangue', 'data-ec--step2-type-param' => 'langueSupport'],
                'class' => Langue::class,
                'choice_label' => 'libelle',
                'expanded' => true,
                'multiple' => true,
                'required' => true,
                'help' => '-'
            ])
            ->add('typeEnseignement', EntityType::class, [
                'placeholder' => 'Choisissez un type d\'enseignement',
                'attr' => ['data-action' => 'change->ec--step2#changeNatureUeEc'],
                'class' => NatureUeEc::class,
                'choice_label' => 'libelle',
                'expanded' => false,
                'multiple' => false,
                'required' => true,
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
