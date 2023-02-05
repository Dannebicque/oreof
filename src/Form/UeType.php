<?php

namespace App\Form;

use App\Entity\TypeEnseignement;
use App\Entity\TypeUe;
use App\Entity\Ue;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ordre')
            ->add('typeUe', EntityType::class, [
                'class' => TypeUe::class,
                'choice_label' => 'libelle',
            ])
            ->add('typeUeTexte')
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
        ]);
    }
}
