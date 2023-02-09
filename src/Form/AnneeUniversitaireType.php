<?php

namespace App\Form;

use App\Entity\AnneeUniversitaire;
use App\Form\Type\YesNoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnneeUniversitaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, [
                'label' => 'Libellé Année Universitaire',
                'attr' => [
                    'placeholder' => 'Ex: 2019-2020',
                ],
            ])
            ->add('annee', ChoiceType::class, [
                'label' => 'Année',
                'choices' => [
                    '2023' => 2023,
                    '2024' => 2024,
                    '2025' => 2025,
                    '2026' => 2026,
                    '2027' => 2027,
                    '2028' => 2028,
                    '2029' => 2029,
                    '2030' => 2030,
                ],
            ])
            ->add('defaut', YesNoType::class, [
                'label' => 'Campagne de collecte DPE active ?',
            ])
            ->add('dateOuvertureDpe', DateType::class, [
                'label' => 'Date d\'ouverture du DPE',
                'widget' => 'single_text',
            ])
            ->add('dateClotureDpe', DateType::class, [
                'label' => 'Date de clôture du DPE',
                'widget' => 'single_text',
            ])
            ->add('dateTransmissionSes', DateType::class, [
                'label' => 'Date limite de transmission des dossiers ',
                'widget' => 'single_text',
            ])
            ->add('dateCfvu', DateType::class, [
                'label' => 'Date de la CFVU ',
                'widget' => 'single_text',
            ])
            ->add('datePublication', DateType::class, [
                'label' => 'Date de publication ',
                'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AnneeUniversitaire::class,
        ]);
    }
}
