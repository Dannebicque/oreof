<?php

namespace App\Form;

use App\Entity\Competence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'attr' => ['maxlength' => 10],
                'help' => 'Le code doit être unique et ne doit pas dépasser 10 caractères.'
                ])
            ->add('libelle', TextType::class, [
                'attr' => ['maxlength' => 255],
                'help' => 'Le libellé de la compétence doit commencer par un verbe d\'action, et ne doit pas dépasser 255 caractères. Doit être cohérente avec la fiche RNCP du diplôme.'
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Competence::class,
        ]);
    }
}
