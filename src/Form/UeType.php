<?php

namespace App\Form;

use App\Entity\TypeEnseignement;
use App\Entity\TypeUe;
use App\Entity\Ue;
use App\Repository\TypeUeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = $options['choices'];


        $builder
            ->add('ordre', TextType::class, [
                'label' => 'Numéro de l\'UE',
            ])
            ->add('typeUe', ChoiceType::class, [
                'choices' => $choices,
                'required' => false,
                'mapped' => false,
            ])//todo: ajouter un champ texte si l'UE n'est pas dans la liste + bouton autre ?
            ->add('typeUeTexte', TextType::class, [
                'label' => 'Type d\'UE si non présent dans la liste',
                'required' => false,
            ])
            ->add('ueObligatoire', EntityType::class, [
                'class' => TypeEnseignement::class,
                'choice_label' => 'libelle',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ue::class,
            'choices' => [],
        ]);
    }
}
