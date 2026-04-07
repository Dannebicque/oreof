<?php

namespace App\Form;

use App\Entity\User;
use App\Enums\TypeRfEnum;
use App\Form\Type\EntityWithAddType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UnitEnum;

class ChangeRfFormationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeRf', EnumType::class, [
                'class' => TypeRfEnum::class,
                'expanded' => true,
                'translation_domain' => 'form',
                'choice_label' => static function (UnitEnum $choice): string {
                    return $choice->getLibelle();
                },
            ])
            ->add('user', EntityWithAddType::class, [
                'class' => User::class,
                'choice_label' => 'display',
                'multiple' => false,
                'autocomplete' => true,
                'expanded' => false,
                'query_builder' => static function ($er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.nom', 'ASC')
                        ->addOrderBy('t.prenom', 'ASC');
                },
                'help_to_add' => 'Saisir l\'email urca de la personne à ajouter.',
                'placeholder' => 'Choisir dans la liste ou choisir "+" pour ajouter un utilisateur',
                'label' => 'Nouveau (co-)responsable de formation',
                'required' => false,
            ])
            ->add('datePriseFonction', DateType::class, [
                'label' => 'Date de prise de fonction',
                'widget' => 'single_text',
                'required' => true,
                'help' => 'Attention : cette date pourra impacter l\'année en cours et/ou l\'année précédente selon les dates de la campagne.',
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaire sur le changement de (co-)responsable de formation',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
