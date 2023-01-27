<?php

namespace App\Form;

use App\Entity\User;
use App\Enums\RoleEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('email')
            ->add('username')
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
