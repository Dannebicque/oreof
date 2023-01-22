<?php

namespace App\Form;

use App\Entity\Formation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeDiplome')
            ->add('mentionTexte')
            ->add('niveauEntree')
            ->add('niveauSortie')
            ->add('inscriptionRNCP')
            ->add('codeRNCP')
            ->add('domaine')
            ->add('mention')
            ->add('responsableMention')
            ->add('sites')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
