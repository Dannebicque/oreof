<?php

namespace App\Form;

use App\Entity\ElementConstitutif;
use App\Form\Type\TextareaWithSaveType;
use App\Form\Type\YesNoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EcStep1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, [
                'label' => 'Libellé',
                'disabled' => true, //non modifiable sauf si Respo_Formation
                'attr' => ['maxlength' => 255]
            ])
            ->add('libelleAnglais', TextType::class, [
                'label' => 'Libellé anglais',
                'attr' => ['maxlength' => 255]
            ])
            ->add('enseignementMutualise', YesNoType::class, [
                'label' => 'Enseignement mutualisé ?',
            ])//si oui ajouter le cboix des formations/Parcours?
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElementConstitutif::class,
        ]);
    }
}
