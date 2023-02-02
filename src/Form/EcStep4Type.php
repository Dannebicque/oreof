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
        //todo: déplacer sur le resp de formation, c'est lui qui initie ca
        $builder
            ->add('modaliteEnseignement', EnumType::class, [
                'label' => 'Modalité d\'enseignement',
                'class' => ModaliteEnseignementEnum::class,
                'expanded' => true,
                'attr' => ['data-action' => 'change->ec--structure#saveModaliteEnseignement'],
            ])
            ->add('ects', NumberType::class, [
                'label' => 'ECTS',
                'attr' => ['data-action' => 'change->ec--structure#saveEcts'],
            ])
            ->add('volumeCmPresentiel', NumberType::class, [
                'label' => 'Volume CM',
                'attr' => ['data-action' => 'change->ec--structure#saveVolume', 'data-ec--structure-type-param' => 'volumeCmPresentiel'],
            ])
            ->add('volumeTdPresentiel', NumberType::class, [
                'label' => 'Volume TD',
                'attr' => ['data-action' => 'change->ec--structure#saveVolume', 'data-ec--structure-type-param' => 'volumeTdPresentiel'],
            ])
            ->add('volumeTpPresentiel', NumberType::class, [
                'label' => 'Volume TP',
                'attr' => ['data-action' => 'change->ec--structure#saveVolume', 'data-ec--structure-type-param' => 'volumeTpPresentiel'],
            ])
            ->add('volumeCmDistanciel', NumberType::class, [
                'label' => 'Volume CM',
                'attr' => ['data-action' => 'change->ec--structure#saveVolume', 'data-ec--structure-type-param' => 'volumeCmDistanciel'],
            ])
            ->add('volumeTdDistanciel',NumberType::class, [
                'label' => 'Volume TD',
                'attr' => ['data-action' => 'change->ec--structure#saveVolume', 'data-ec--structure-type-param' => 'volumeTdDistanciel'],
            ])
            ->add('volumeTpDistanciel', NumberType::class, [
                'label' => 'Volume TP',
                'attr' => ['data-action' => 'change->ec--structure#saveVolume', 'data-ec--structure-type-param' => 'volumeTpDistanciel'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElementConstitutif::class,
        ]);
    }
}
