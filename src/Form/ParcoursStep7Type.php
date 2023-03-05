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
                'attr' => ['rows' => 20, 'maxlength' => 3000, 'data-action' => 'change->parcours--step7#savePoursuitesEtudes'],
                'help' =>'-',
            ])
            ->add('debouches', TextareaAutoSaveType::class, [
                'attr' => ['rows' => 10, 'maxlength' => 3000, 'data-action' => 'change->parcours--step7#saveDebouches'],
                'help' => '-',
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
