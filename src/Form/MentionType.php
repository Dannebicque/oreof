<?php

namespace App\Form;

use App\Entity\Domaine;
use App\Entity\Mention;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class MentionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        //todo: fitlrer par établissement du connecté...
        $builder
            ->add('typeDiplome', ChoiceType::class, [
                'choices' => $options['typesDiplomes'],
                'translation_domain' => 'enum',
                'label' => 'Type de diplôme',
                'attr' => ['data-action' => 'change->formation#changeTypeDiplome']
            ])
            ->add('libelle', TextType::class, [
                'label' => 'Libellé',
                'required' => true,
            ])
            ->add('sigle', TextType::class, [
                'label' => 'Sigle',
                'required' => true,

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
            'typesDiplomes' => []
        ]);
    }
}
