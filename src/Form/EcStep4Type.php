<?php

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

        if ($modalites !== null && $modalitesParcours === ModaliteEnseignementEnum::HYBRIDE) {
            $builder->add('modaliteEnseignement', EnumType::class, [
                'help' => '-',
                'class' => ModaliteEnseignementEnum::class,
                'choice_label' => fn($choice) => match ($choice) {
                    modaliteEnseignementEnum::PRESENTIELLE => 'En présentiel',
                    modaliteEnseignementEnum::DISTANCIELLE => 'En distanciel',
                    modaliteEnseignementEnum::HYBRIDE => 'Hybride',
                },
                'expanded' => true,
                'attr' => ['data-action' => !$isModal ? 'change->ec--structure#saveModaliteEnseignement' : 'change->ec--structureparcours#changeModalite'],
            ]);
        }

        $builder
            ->add('ects', NumberType::class, [
                'help' => '-',
                'attr' => ['data-action' => !$isModal ? 'change->ec--structure#saveEcts' : ''],
            ]);

            $builder->add('volumeCmPresentiel', NumberType::class, [
                'help' => '-',
                'attr' => [
                    'data-action' => !$isModal ? 'change->ec--structure#saveVolume' : '',
                    'data-ec--structure-type-param' => 'volumeCmPresentiel'
                ],
            ])
                ->add('volumeTdPresentiel', NumberType::class, [
                    'help' => '-',
                    'attr' => [
                        'data-action' => !$isModal ? 'change->ec--structure#saveVolume' : '',
                        'data-ec--structure-type-param' => 'volumeTdPresentiel'
                    ],
                ])
                ->add('volumeTpPresentiel', NumberType::class, [
                    'help' => '-',
                    'attr' => [
                        'data-action' => !$isModal ? 'change->ec--structure#saveVolume' : '',
                        'data-ec--structure-type-param' => 'volumeTpPresentiel'
                    ],
                ])



            ->add('volumeCmDistanciel', NumberType::class, [
                'help' => '-',
                'attr' => [
                    'data-action' => !$isModal ? 'change->ec--structure#saveVolume' : '',
                    'data-ec--structure-type-param' => 'volumeCmDistanciel'
                ],
            ])
                ->add('volumeTdDistanciel', NumberType::class, [
                    'help' => '-',
                    'attr' => [
                        'data-action' => !$isModal ? 'change->ec--structure#saveVolume' : '',
                        'data-ec--structure-type-param' => 'volumeTdDistanciel'
                    ],
                ])
                ->add('volumeTpDistanciel', NumberType::class, [
                    'help' => '-',
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
