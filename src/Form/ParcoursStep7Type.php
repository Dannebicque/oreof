<?php

namespace App\Form;

use App\Entity\Parcours;
use App\Form\Type\TextareaAutoSaveType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParcoursStep7Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('poursuitesEtudes', TextareaAutoSaveType::class, [
                'label' => 'Poursuites d\'études envisageables',
                'attr' => ['rows' => 20, 'maxlength' => 3000, 'data-action' => 'change->parcours--step7#savePoursuitesEtudes'],
                'help' => 'Indiquez en 3000 caractères maximum quelles sont les poursuites d’études envisageables.',
            ])
            ->add('debouches', TextareaAutoSaveType::class, [
                'label' => 'Débouchés',
                'attr' => ['rows' => 10, 'maxlength' => 3000, 'data-action' => 'change->parcours--step7#saveDebouches'],
                'help' => 'Indiquez ici les principaux débouchés professionnels accessibles à l’issue de cette formation.',
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
