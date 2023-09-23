<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/FicheMatiereStep3Type.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 05/03/2023 16:19
 */

namespace App\Form;

use App\Entity\FicheMatiere;
use App\Form\Type\FloatType;
use App\Form\Type\TextareaAutoSaveType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FicheMatiereStep4HdType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('volumeCmPresentiel', FloatType::class, [
            'html5' => true,
            'scale' => 1,
            'required' => false,
            'attr' => [
                'data-action' => 'change->fichematiere--step4#saveVolume',
                'data-fichematiere--step4-type-param' => 'volumeCmPresentiel'
            ],
        ])
            ->add('volumeTdPresentiel', FloatType::class, [
                'html5' => true,
                'scale' => 1,
                'required' => false,
                'attr' => [
                    'data-action' => 'change->fichematiere--step4#saveVolume',
                    'data-fichematiere--step4-type-param' => 'volumeTdPresentiel'
                ],
            ])
            ->add('volumeTpPresentiel', FloatType::class, [
                'html5' => true,
                'scale' => 1,
                'required' => false,
                'attr' => [
                    'data-action' => 'change->fichematiere--step4#saveVolume',
                    'data-fichematiere--step4-type-param' => 'volumeTpPresentiel'
                ],
            ]);
        $builder->add('volumeCmDistanciel', FloatType::class, [
            'html5' => true,
            'scale' => 1,
            'required' => false,
            'attr' => [
                'data-action' => 'change->fichematiere--step4#saveVolume',
                'data-fichematiere--step4-type-param' => 'volumeCmDistanciel'
            ],
        ])
            ->add('volumeTdDistanciel', FloatType::class, [
                'html5' => true,
                'scale' => 1,
                'required' => false,
                'attr' => [
                    'data-action' => 'change->fichematiere--step4#saveVolume',
                    'data-fichematiere--step4-type-param' => 'volumeTdDistanciel'
                ],
            ])
            ->add('volumeTpDistanciel', FloatType::class, [
                'html5' => true,
                'scale' => 1,
                'required' => false,
                'attr' => [
                    'data-action' => 'change->fichematiere--step4#saveVolume',
                    'data-fichematiere--step4-type-param' => 'volumeTpDistanciel'
                ],
            ])
            ->add('ects', FloatType::class, [
                'html5' => true,
                'scale' => 1,
                'required' => false,
                'attr' => [
                    'data-action' => 'change->fichematiere--step4#saveEcts',
                    'data-fichematiere--step4-type-param' => 'ects'
                ],
            ]);


        $builder->add('volumeTe', FloatType::class, [
            'html5' => true,
            'scale' => 1,
            'required' => false,
            'attr' => [
                'data-action' => 'change->fichematiere--step4#saveVolume',
                'data-fichematiere--step4-type-param' => 'volumeTe'
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FicheMatiere::class,
            'translation_domain' => 'form'
        ]);
    }
}
