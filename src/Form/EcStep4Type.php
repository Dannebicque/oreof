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
            ])
            ->add('ects', NumberType::class, [
                'label' => 'ECTS',
            ])
            ->add('volumeCmPresentiel', NumberType::class, [
                'label' => 'Volume CM',
            ])
            ->add('volumeTdPresentiel', NumberType::class, [
                'label' => 'Volume TD',
            ])
            ->add('volumeTpPresentiel', NumberType::class, [
                'label' => 'Volume TP',
            ])
            ->add('volumeCmDistanciel', NumberType::class, [
                'label' => 'Volume CM',
            ])
            ->add('volumeTdDistanciel',NumberType::class, [
                'label' => 'Volume TD',
            ])
            ->add('volumeTpDistanciel', NumberType::class, [
                'label' => 'Volume TP',
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
