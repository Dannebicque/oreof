<?php

namespace App\Form;

use App\Entity\ElementConstitutif;
use App\Form\Type\TextareaWithSaveType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EcStep2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('description', TextareaWithSaveType::class, [
                'label' => 'Description',
                'attr' => ['maxlength' => 3000]
            ])
           //todo: ajtouer les choix
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElementConstitutif::class,
        ]);
    }
}
