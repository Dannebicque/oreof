<?php

namespace App\Form;

use App\Entity\Parcours;
use App\Form\Type\TextareaWithSaveType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParcoursStep5Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prerequis', TextareaWithSaveType::class, [
                'label' => 'Prérequis recommandés',
                'attr' => ['rows' => 15, 'maxlength' => 3000],
                'help' => 'Indiquez ici si des prérequis pédagogiques sont conseillés pour réussir dans cette formation',
                'button_action' => 'click->parcours--step5#savePrerequis',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Parcours::class,
        ]);
    }
}
