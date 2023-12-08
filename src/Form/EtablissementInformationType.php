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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EtablissementInformation::class,
        ]);
    }
}
