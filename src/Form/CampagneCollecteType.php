<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/AnneeUniversitaireType.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/02/2023 22:12
 */

namespace App\Form;

use App\Entity\AnneeUniversitaire;
use App\Entity\CampagneCollecte;
use App\Form\Type\YesNoType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampagneCollecteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, [
                'label' => 'Libellé de la campagne de collecte',
                'attr' => [
                    'placeholder' => 'Ex: Accréditation 2024, 2025-2026, ...',
                ],
            ])
            ->add('anneeUniversitaire', EntityType::class, [
                'class' => AnneeUniversitaire::class,
                'label' => 'Année Universitaire',
                'choice_label' => 'libelle',
                'required' => true,
                'query_builder' => static function ($er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.libelle', 'ASC');
                },
            ])
            ->add('codeApogee', TextType::class, [
                'label' => 'Code Apogée',
                'attr' => ['maxlength' => 1],
                'required' => true,
            ])
            ->add('defaut', YesNoType::class, [
                'label' => 'Campagne de collecte DPE active ?',
            ])
            ->add('dateOuvertureDpe', DateType::class, [
                'label' => 'Date d\'ouverture de la campagne de collecte DPE',
                'widget' => 'single_text',
            ])
            ->add('dateClotureDpe', DateType::class, [
                'label' => 'Date de clôture de la campagne de collecte DPE',
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
            ->add('couleur', ChoiceType::class, [
                'label' => 'Couleur de la campagne',
                'choices' => [
                    'Bleu' => 'primary',
                    'Vert' => 'success',
                    'Rouge' => 'danger',
                    'Jaune' => 'warning',
                    'Gris' => 'secondary',
                    'Noir' => 'dark',
                    'Blanc' => 'light',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CampagneCollecte::class,
            'translation_domain' => 'form'
        ]);
    }
}
