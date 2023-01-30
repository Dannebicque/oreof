<?php

namespace App\Form;

use App\Entity\ElementConstitutif;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EcStep5Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle')
            ->add('libelleAnglais')
            ->add('enseignementMutualise')
            ->add('description')
            ->add('objectifs')
            ->add('modaliteEnseignement')
            ->add('ects')
            ->add('volumeCmPresentiel')
            ->add('volumeTdPresentiel')
            ->add('volumeTpPresentiel')
            ->add('volumeCmDistanciel')
            ->add('volumeTdDistanciel')
            ->add('volumeTpDistanciel')
            ->add('ue')
            ->add('competences')
            ->add('responsableEc')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElementConstitutif::class,
        ]);
    }
}
