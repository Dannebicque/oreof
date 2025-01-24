<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('denomination', TextType::class, [
                'label' => 'Dénomination du contact',
                'attr' => [
                    'maxlength' => 255,
                ],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'maxlength' => 50,
                ],
                'required' => false,
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'maxlength' => 50,
                ],
                'required' => false,
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'maxlength' => 20,
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'maxlength' => 255,
                ],
            ])
            ->add('adresse', AdresseType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
