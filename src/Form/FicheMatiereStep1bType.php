<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/FicheMatiereStep1Type.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Form;

use App\Entity\FicheMatiere;
use App\Entity\User;
use App\Form\Type\YesNoType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class FicheMatiereStep1bType extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('enseignementMutualise', YesNoType::class, [
            ]);
//            ->add('isCmPresentielMutualise', YesNoType::class, [
//                'attr' => [
//                    'data-action' => 'change->fichematiere--step1#isMutualise',
//                    'data-fichematiere--step1-type-param' => 'isCmPresentielMutualise'
//                ],
//            ])
//            ->add('isTdPresentielMutualise', YesNoType::class, [
//                'attr' => [
//                    'data-action' => 'change->fichematiere--step1#isMutualise',
//                    'data-fichematiere--step1-type-param' => 'isTdPresentielMutualise'
//                ],
//            ])
//            ->add('isTpPresentielMutualise', YesNoType::class, [
//                'attr' => [
//                    'data-action' => 'change->fichematiere--step1#isMutualise',
//                    'data-fichematiere--step1-type-param' => 'isTpPresentielMutualise'
//                ],
//            ])
//            ->add('isCmDistancielMutualise', YesNoType::class, [
//                'attr' => [
//                    'data-action' => 'change->fichematiere--step1#isMutualise',
//                    'data-fichematiere--step1-type-param' => 'isCmDistancielMutualise'
//                ],
//                'help' => '-'
//            ])
//            ->add('isTdDistancielMutualise', YesNoType::class, [
//                'attr' => [
//                    'data-action' => 'change->fichematiere--step1#isMutualise',
//                    'data-fichematiere--step1-type-param' => 'isTdDistancielMutualise'
//                ],
//                'help' => '-'
//            ])
//            ->add('isTpDistancielMutualise', YesNoType::class, [
//                'attr' => [
//                    'data-action' => 'change->fichematiere--step1#isMutualise',
//                    'data-fichematiere--step1-type-param' => 'isTpDistancielMutualise'
//                ],
//                'help' => '-'
//            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FicheMatiere::class,
            'translation_domain' => 'form'
        ]);
    }
}
