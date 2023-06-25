<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/UeType.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 21:36
 */

namespace App\Form;

use App\Entity\NatureUeEc;
use App\Entity\TypeUe;
use App\Entity\Ue;
use App\Form\Type\FloatType;
use App\Repository\TypeUeRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $typeDiplome = $options['typeDiplome'];


        $builder
            ->add('libelle', TextType::class, [
                'attr' => [
                    'maxlength' => 255,
                ],
                'required' => false
            ])
            ->add('ects', FloatType::class, [
                'required' => false
            ])
            ->add('typeUe', EntityType::class, [
                'class' => TypeUe::class,
                'choice_label' => 'libelle',
                'query_builder' => fn (
                    TypeUeRepository $typeUeRepository
                ) => $typeUeRepository->findByTypeDiplome($typeDiplome),
                'required' => false,
            ])
            ->add('typeUeTexte', TextType::class, [
                'attr' => [
                    'maxlength' => 100,
                ],
                'required' => false,
                'mapped' => false,
            ])
            ->add('natureUeEc', EntityType::class, [
                'class' => NatureUeEc::class,
                'choice_label' => 'libelle',
                'attr' => ['data-action' => 'change->ue#changeNatureUe'],
                'query_builder' => function (EntityRepository $qb) {
                    return $qb->createQueryBuilder('n')
                        ->orderBy('n.libelle', 'ASC');
                },
                'choice_attr' => function ($choice) {
                    return ['data-choix' => $choice->isChoix() ? 'true' : 'false', 'data-libre' => $choice->isLibre() ? 'true' : 'false'];
                },
                'required' => false,
            ])
            ->add('descriptionUeLibre', TextareaType::class, [
                'attr' => [
                    'maxlength' => 255,
                    'rows' => 5,
                ],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ue::class,
            'translation_domain' => 'form',
            'typeDiplome' => null,
        ]);
    }
}
