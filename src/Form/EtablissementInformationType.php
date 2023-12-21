<?php

namespace App\Form;

use App\Entity\EtablissementInformation;
use App\Form\Type\TextareaAutoSaveType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EtablissementInformationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('calendrier_universitaire', TextareaAutoSaveType::class, [
                'label' => 'Calendrier universitaire',
                'required' => true,
                'attr' => ['maxlength' => 2500]
            ])
            ->add('calendrier_inscription', TextareaAutoSaveType::class, [
                'label' => "Calendrier d'inscription",
                'required' => true,
                'attr' => ['maxlength' => 2500]
            ])
            ->add('informations_pratiques', TextareaAutoSaveType::class, [
                'label' => 'Informations pratiques',
                'required' => true,
                'attr' => ['maxlength' => 2500]
            ])
            ->add('restauration', TextareaAutoSaveType::class, [
                'label' => 'Restauration',
                'required' => true,
                'attr' => ['maxlength' => 2500]
            ])
            ->add('hebergement', TextareaAutoSaveType::class, [
                'label' => 'Hébergement',
                'required' => true,
                'attr' => ['maxlength' => 2500]
            ])
            ->add('transport', TextareaAutoSaveType::class, [
                'label' => 'Transport',
                'required' => true,
                'attr' => ['maxlength' => 2500]
            ])
            ->add('descriptifHautPage', TextareaAutoSaveType::class, [
                'label' => 'Texte affiché en haut des pages parcours',
                'help' => 'Ce texte sera affiché par défaut en haut des pages parcours. Il peut être remplacé par un texte spécifique à chaque parcours.',
                'required' => true,
                'attr' => ['maxlength' => 2500]
            ])
            ->add('descriptifBasPage', TextareaAutoSaveType::class, [
                'label' => 'Texte affiché en bas des pages parcours',
                'help' => 'Ce texte sera affiché par défaut en bas des pages parcours. Il peut être remplacé par un texte spécifique à chaque parcours.',
                'required' => true,
                'attr' => ['maxlength' => 2500]
            ])
            ->add('textLas1', TextareaAutoSaveType::class, [
                'label' => 'Texte affiché si le parcours est une LAS1',
                'required' => true,
                'attr' => ['maxlength' => 2500]
            ])
            ->add('textLas2', TextareaAutoSaveType::class, [
                'label' => 'Texte affiché si le parcours est une LAS2 ou 3, avec les règles pour accéder à la LAS2',

                'required' => true,
                'attr' => ['maxlength' => 2500]
            ])
            ->add('textLas3', TextareaAutoSaveType::class, [
                'label' => 'Texte affiché si le parcours est une LAS1-2 ou 3, avec les règles pour accéder à la LAS3',

                'required' => true,
                'attr' => ['maxlength' => 2500]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EtablissementInformation::class,
        ]);
    }
}
