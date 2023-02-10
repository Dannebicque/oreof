<?php

namespace App\Form;

use App\Entity\User;
use App\Enums\CentreGestionEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UnitEnum;

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
                'choice_label' => static function (UnitEnum $choice): string {
                    return $choice->libelle();
                },
                'placeholder' => 'Indiquez un centre de gestion',
                'required' => true,
                'mapped' => false,
                'attr' => ['data-action' => 'change->register#changeCentre']
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
