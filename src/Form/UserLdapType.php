<?php

namespace App\Form;

use App\Entity\User;
use App\Enums\CentreGestionEnum;
use App\Enums\RoleEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserLdapType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Adresse email',
                'help' => 'Adresse email URCA',
                'attr' => ['maxlength' => 255]
            ])
            ->add('centreDemande', EnumType::class, [
                'class' => CentreGestionEnum::class,
                'choice_label' => static function (\UnitEnum $choice): string {
                    return $choice->libelle();
                },
                'placeholder' => 'Indiquez un centre de gestion',
                'required' => true,
                'attr' => ['data-action' => 'change->register#changeCentre']
            ])


            ->add('role', EnumType::class, [
                'class' => RoleEnum::class,
                'choice_label' => static function (\UnitEnum $choice): string {
                    return $choice->libelle();
                },
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
