<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/FicheMatiereType.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 21:42
 */

namespace App\Form;

use App\Entity\ElementConstitutif;
use App\Entity\TypeEc;
use App\Repository\TypeEcRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElementConstitutifEnfantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $typeDiplome = $options['typeDiplome'];
        $formation = $options['formation'];

        $builder
            ->add('typeEc', EntityType::class, [
                'class' => TypeEc::class,
                'autocomplete' => true,
                'choice_label' => 'libelle',
                'query_builder' => fn (
                    TypeEcRepository $typeEcRepository
                ) => $typeEcRepository->findByTypeDiplomeAndFormationBuilder($typeDiplome, $formation),
                'required' => false,
            ])
            ->add('typeEcTexte', TextType::class, [
                'attr' => [
                    'maxlength' => 100,
                ],
                'required' => false,
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElementConstitutif::class,
            'translation_domain' => 'form',
            'typeDiplome' => null,
            'formation' => null,
        ]);
    }
}
