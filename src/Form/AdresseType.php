<?php

namespace App\Form;

use App\Entity\Adresse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdresseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('adresse1', TextType::class, [
                'label' => 'Adresse',
                'help' => 'Numéro, rue, ...',
                'attr' => [
                    'maxlength' => 255,
                ],
            ])
            ->add('adresse2', TextType::class, [
                'label' => 'Complément d\'adresse',
                'help' => 'Boîte postale, bâtiment, ...',
                'attr' => [
                    'maxlength' => 255,
                ],
                'required' => false,
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code postal',
                'attr' => [
                    'maxlength' => 5,
                ],
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'attr' => [
                    'maxlength' => 100,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Adresse::class,
        ]);
    }
}
