<?php

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
    private array $choices;
    public function __construct(RoleRepository $roleRepository)
    {
        $this->choices = [];
        $roles = $roleRepository->findByAll();
        foreach ($roles as $role) {
            $this->choices[$role->getCodeRole()] = $role->getLibelle();
        }
    }


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

            ->add('role', ChoiceType::class, [
                'choices' => $this->choices,
                'label' => 'Droits',
                'placeholder' => 'Indiquez les droits accordés',
                'required' => true,
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
