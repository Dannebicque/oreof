<?php

namespace App\Form;

use App\Entity\ComposanteInformation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ComposanteInformationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('restauration', TextType::class, [
                'label' => 'Restauration',
                'required' => true
            ])
            ->add('hebergement', TextType::class, [
                'label' => 'Hébergement',
                'required' => true
            ])
            ->add('transport', TextType::class, [
                'label' => 'Transport',
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ComposanteInformation::class,
        ]);
    }
}
