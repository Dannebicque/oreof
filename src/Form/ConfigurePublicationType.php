<?php

namespace App\Form;

use App\Entity\CampagneCollecte;
use App\Enums\ConfigurationPublicationEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurePublicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enablePublication', CheckboxType::class, [
                'label' => 'Activer la publication ?',
                'required' => true
            ])
            ->add(ConfigurationPublicationEnum::MAQUETTE->value, ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Publier la Maquette',
                'choices' => [
                    'Sélectionner une option...' => null,
                    'Oui' => true,
                    'Non' => false
                ],
                'choice_attr' => [
                    'Sélectionner une option...' => ($options['hasPublishedMaquette'] === 'none') ? ['selected' => ''] : [],
                    'Oui' => ($options['hasPublishedMaquette'] === true) ? ['selected' => ''] : [],
                    'Non' => ($options['hasPublishedMaquette'] === false) ? ['selected' => ''] : []
                ]
            ])
            ->add(ConfigurationPublicationEnum::MCCC->value, ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Publier les MCCC (PDF)',
                'choices' => [
                    'Sélectionner une option...' => null,
                    'Oui' => true,
                    'Non' => false
                ],
                'choice_attr' => [
                    'Sélectionner une option... ' => ($options['hasPublishedMccc'] === 'none') ? ['selected' => ''] : [],
                    'Oui' => ($options['hasPublishedMccc'] === true) ? ['selected' => ''] : [],
                    'Non' => ($options['hasPublishedMccc'] === false) ? ['selected' => ''] : []
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CampagneCollecte::class,
            'hasPublishedMaquette' => 'none',
            'hasPublishedMccc' => 'none'
        ]);
    }
}
