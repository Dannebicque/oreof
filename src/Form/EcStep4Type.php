<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/EcStep4Type.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Form;

use App\Entity\ElementConstitutif;
use App\Enums\ModaliteEnseignementEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EcStep4Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isModal = $options['isModal'];
        $modalites = $options['data']->getModaliteEnseignement();
        $modalitesParcours = $options['data']->getParcours()->getModalitesEnseignement();

        if ($modalitesParcours === ModaliteEnseignementEnum::HYBRIDE || $modalites === null || $modalites === ModaliteEnseignementEnum::NON_DEFINI) {
            $builder->add('modaliteEnseignement', EnumType::class, [
                'class' => ModaliteEnseignementEnum::class,
                'choice_label' => fn ($choice) => match ($choice) {
                    modaliteEnseignementEnum::NON_DEFINI => 'Choisir une modalité',
                    modaliteEnseignementEnum::PRESENTIELLE => 'En présentiel',
                    modaliteEnseignementEnum::DISTANCIELLE => 'En distanciel',
                    modaliteEnseignementEnum::HYBRIDE => 'Hybride',
                },
                'expanded' => false,
                'attr' => ['data-action' => !$isModal ? 'change->ec--structure#saveModaliteEnseignement' : 'change->ec--structureparcours#changeModalite'],
            ]);
        }

        $builder
            ->add('ects', NumberType::class, [
                'html5' => true,
                'scale' => 0,
                'attr' => ['data-action' => !$isModal ? 'change->ec--structure#saveEcts' : ''],
            ]);

        $builder->add('volumeCmPresentiel', NumberType::class, [
            'html5' => true,
            'scale' => 1,
            'attr' => [
                'data-action' => !$isModal ? 'change->ec--structure#saveVolume' : '',
                'data-ec--structure-type-param' => 'volumeCmPresentiel'
            ],
        ])
            ->add('volumeTdPresentiel', NumberType::class, [
                'html5' => true,
                'scale' => 1,
                'attr' => [
                    'data-action' => !$isModal ? 'change->ec--structure#saveVolume' : '',
                    'data-ec--structure-type-param' => 'volumeTdPresentiel'
                ],
            ])
            ->add('volumeTpPresentiel', NumberType::class, [
                'html5' => true,
                'scale' => 1,
                'attr' => [
                    'data-action' => !$isModal ? 'change->ec--structure#saveVolume' : '',
                    'data-ec--structure-type-param' => 'volumeTpPresentiel'
                ],
            ])
            ->add('volumeCmDistanciel', NumberType::class, [
                'html5' => true,
                'scale' => 1,
                'attr' => [
                    'data-action' => !$isModal ? 'change->ec--structure#saveVolume' : '',
                    'data-ec--structure-type-param' => 'volumeCmDistanciel',
                ],
            ])
            ->add('volumeTdDistanciel', NumberType::class, [
                'html5' => true,
                'scale' => 1,
                'attr' => [
                    'data-action' => !$isModal ? 'change->ec--structure#saveVolume' : '',
                    'data-ec--structure-type-param' => 'volumeTdDistanciel'
                ],
            ])
            ->add('volumeTpDistanciel', NumberType::class, [
                'html5' => true,
                'scale' => 1,
                'attr' => [
                    'data-action' => !$isModal ? 'change->ec--structure#saveVolume' : '',
                    'data-ec--structure-type-param' => 'volumeTpDistanciel'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElementConstitutif::class,
            'isModal' => false,
            'translation_domain' => 'form'
        ]);
    }
}
