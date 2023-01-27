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

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',TextType::class, [
                'required' => true,
                'label' => 'Nom',
            ])
            ->add('prenom',TextType::class, [
                'required' => true,
                'label' => 'Prénom',
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Email URCA',
                'help' => 'Email URCA valide'
            ])
            ->add('centreDemande', EnumType::class, [
                'class' => CentreGestionEnum::class,
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
                'placeholder' => 'Indiquez les droits souhaités',
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
