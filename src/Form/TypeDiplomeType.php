<?php

namespace App\Form;

use App\Entity\TypeDiplome;
use App\TypeDiplome\TypeDiplomeRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('hasMemoire')
            ->add('hasStage')
            ->add('hasSituationPro')
            ->add('hasProjet');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TypeDiplome::class,
            'translation_domain' => 'form'
        ]);
    }
}
