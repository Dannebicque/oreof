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
use App\Entity\NatureUeEc;
use App\Entity\TypeEc;
use App\Repository\NatureUeEcRepository;
use App\Repository\TypeEcRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElementConstitutifType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $typeDiplome = $options['typeDiplome'];
        $formation = $options['formation'];
        $isAdmin = $options['isAdmin'];

        if ($isAdmin) {
            $builder
                ->add('ordre', IntegerType::class, [])
                ->add('code', TextType::class, [
                'required' => false]);
        }

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
            ->add('natureUeEc', EntityType::class, [
                'class' => NatureUeEc::class,
                'choice_label' => 'libelle',
                'query_builder' => fn (
                    NatureUeEcRepository $natureUeEcRepository
                ) => $natureUeEcRepository->findByBuilder(NatureUeEc::Nature_EC),
                'required' => false,
                'placeholder' => 'Choisissez une nature...',
                'choice_attr' => function ($choice) {
                    return ['data-choix' => $choice->isChoix() ? 'true' : 'false'];
                },
                'attr' => ['data-action' => 'change->ec--manage#changeNatureEc'],
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
            'isAdmin' => false,
        ]);
    }
}
