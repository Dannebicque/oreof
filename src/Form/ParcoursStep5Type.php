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
                'attr' => ['rows' => 15, 'maxlength' => 3000, 'data-action' => 'change->parcours--step5#savePrerequis'],
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
