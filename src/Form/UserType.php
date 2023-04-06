<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/UserType.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 23/02/2023 16:34
 */

namespace App\Form;

use App\Entity\User;
use App\Repository\RoleRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UnitEnum;

class UserType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'required' => true,
                'label' => 'Login',
                'help' => 'Login URCA',
                'attr' => ['maxlength' => 10]
            ])
            ->add('civilite', ChoiceType::class, [
                'choices' => [
                    'Monsieur' => 'M',
                    'Madame' => 'Mme',
                ],
                'label' => 'Civilité',
                'placeholder' => 'Indiquez votre civilité',
                'required' => true,
                'mapped' => false,
            ])
            ->add('nom', TextType::class, [
                'required' => true,
                'label' => 'Nom',
                'attr' => ['maxlength' => 50]
            ])
            ->add('prenom', TextType::class, [
                'required' => true,
                'label' => 'Prénom',
                'attr' => ['maxlength' => 50]
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Adresse email',
                'help' => 'Adresse email URCA',
                'attr' => ['maxlength' => 255]
            ])
            ->add('telFixe', TextType::class, [
                'required' => false,
                'label' => 'Téléphone fixe',
                'attr' => ['maxlength' => 10]
            ])
            ->add('telPortable', TextType::class, [
                'required' => false,
                'label' => 'Téléphone portable',
                'attr' => ['maxlength' => 10]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'form'
        ]);
    }
}
