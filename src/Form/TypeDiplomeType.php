<?php

namespace App\Form;

use App\Entity\TypeDiplome;
use App\Form\Type\TextareaAutoSaveType;
use App\Form\Type\YesNoType;
use App\TypeDiplome\TypeDiplomeRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeDiplomeType extends AbstractType
{
    public function __construct(
        private TypeDiplomeRegistry $typeDiplomeRegistry
    )
    {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle')
            ->add('libelle_court')
            ->add('codeApogee', TextType::class, [
                'label' => 'Code Apogée',
                'attr' => ['maxlength' => 1],
                'required' => true,
            ])
            ->add('codifIntermediaire', YesNoType::class, [
                'label' => 'Codification intermédiaire',
                'required' => true,
            ])
            ->add('modalites_admission', TextareaAutoSaveType::class, [
                'label' => "Modalités d'admission",
                'required' => true,
                'attr' => [
                    'rows' => 6,
                    'maxlength' => 3000
                ]
            ])
            ->add('prerequis_obligatoires', TextareaAutoSaveType::class, [
                'label' => "Prérequis obligatoires",
                'required' => false,
                'attr' => [
                    'rows' => 6,
                    'maxlength' => 3000
                ]
            ])
            ->add('presentationFormation', TextareaAutoSaveType::class, [
                'label' => "Présentation des formations",
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'maxlength' => 3000
                ]
            ])
            ->add('insertionProfessionnelle', TextareaAutoSaveType::class, [
                'label' => 'Devenir des diplômés',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'maxlength' => 3000
                ]
            ])
            ->add('semestreDebut')
            ->add('semestreFin')
            ->add('nbUeMin')
            ->add('nbUeMax')
            ->add('nbEctsMaxUe')
            ->add('nbEcParUe')
            ->add('ModeleMcc', ChoiceType::class, [
                'choices' => $this->typeDiplomeRegistry->getChoices(),
            ])
            ->add('debutSemestreFlexible')
            ->add('hasMemoire', YesNoType::class)
            ->add('hasStage', YesNoType::class)
            ->add('hasSituationPro', YesNoType::class)
            ->add('hasProjet', YesNoType::class)
            ->add('ectsObligatoireSurEc', YesNoType::class, ['empty_data' => true])
            ->add('mcccObligatoireSurEc', YesNoType::class, ['empty_data' => true])
            ->add('controleAssiduite', YesNoType::class, ['empty_data' => true]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TypeDiplome::class,
            'translation_domain' => 'form'
        ]);
    }
}
