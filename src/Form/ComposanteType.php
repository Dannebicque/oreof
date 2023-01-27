<?php

namespace App\Form;

use App\Entity\Composante;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ComposanteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle')
            ->add('directeur', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'display',
                'multiple' => false,
                'expanded' => false,
                'label' => 'Directeur de composante',
            ])
            ->add('responsableDpe', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'display',
                'multiple' => false,
                'expanded' => false,
                'label' => 'Responsable "DPE" de la composante',
                'help' => 'Personne responsable de la validation des DPE des formations de la composante'
            ])
            ->add('adresse', AdresseType::class, [
                'label' => 'Adresse du site principal de la composante',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Composante::class,
        ]);
    }
}
