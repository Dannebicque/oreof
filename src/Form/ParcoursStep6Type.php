<?php

namespace App\Form;

use App\Entity\Parcours;
use App\Form\Type\TextareaWithSaveType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParcoursStep6Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('poursuitesEtudes', TextareaWithSaveType::class, [
                'label' => 'Poursuites d\'études envisageables',
                'attr' => ['rows' => 5, 'maxlength' => 3000],
                'help' => 'Indiquez ici...',
                'button_action' => 'click->parcours--step5#savePoursuitesEtudes',
            ])
            ->add('debouches', TextareaWithSaveType::class, [
                'label' => 'Débouchés',
                'attr' => ['rows' => 5, 'maxlength' => 3000],
                'help' => 'Indiquez ici...',
                'button_action' => 'click->parcours--step5#saveDebouches',
            ])
            ->add('code', TextType::class, [
                'label' => 'Débouchés',
                'mapped' => false,
                'help' => 'Indiquez ici...',
            ])
            ->add('libelleCode', TextType::class, [
                'label' => 'Débouchés',
                'mapped' => false,
                'help' => 'Indiquez ici...',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Parcours::class,
        ]);
    }
}
