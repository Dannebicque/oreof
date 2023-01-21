<?php

namespace App\Form;

use App\Entity\Domaine;
use App\Entity\Mention;
use App\Entity\TypeDiplome;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MentionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        //todo: fitlrer par établissement du connecté...
        $builder
            ->add('libelle')
            ->add('sigle')
            ->add('typeDiplome', EntityType::class, [
                'class' => TypeDiplome::class,
                'choice_label' => 'libelle',
            ])
            ->add('domaine', EntityType::class, [
                'class' => Domaine::class,
                'choice_label' => 'libelle',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mention::class,
        ]);
    }
}
