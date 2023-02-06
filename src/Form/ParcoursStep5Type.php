<?php

namespace App\Form;

use App\Entity\Parcours;
use App\Form\Type\TextareaAutoSaveType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParcoursStep5Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prerequis', TextareaAutoSaveType::class, [
                'label' => 'Prérequis recommandés',
                'attr' => ['rows' => 15, 'maxlength' => 3000, 'data-action' => 'change->parcours--step5#savePrerequis'],
                'help' => 'Indiquez ici si des prérequis pédagogiques sont conseillés pour réussir dans cette formation',
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
