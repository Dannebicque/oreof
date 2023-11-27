<?php

namespace App\Form;

use App\Entity\EtablissementInformation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EtablissementInformationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('calendrier_universitaire', TextareaType::class, [
                'label' => 'Calendrier universitaire',
                'required' => true
            ])
            ->add('calendrier_inscription', TextareaType::class, [
                'label' => "Calendrier d'inscription",
                'required' => true
            ])
            ->add('informations_pratiques', TextareaType::class, [
                'label' => 'Informations pratiques',
                'required' => true
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
