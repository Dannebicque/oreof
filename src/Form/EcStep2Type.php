<?php

namespace App\Form;

use App\Entity\ElementConstitutif;
use App\Entity\Langue;
use App\Entity\TypeEnseignement;
use App\Form\Type\TextareaAutoSaveType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EcStep2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('description', TextareaAutoSaveType::class, [
                'attr' => ['data-action' => 'change->ec--step2#saveDescription', 'maxlength' => 3000, 'rows' => 20, 'class' => 'tinyMce'],
                'help' => '-'
            ])
            ->add('langueDispense', EntityType::class, [
                'attr' => ['data-action' => 'change->ec--step2#changeLangue', 'data-ec--step2-type-param' => 'langueDispense' ],
                'class' => Langue::class,
                'choice_label' => 'libelle',
                'expanded' => true,
                'multiple' => true,
                'required' => true,
                'help' => '-'
            ])
            ->add('langueSupport', EntityType::class, [
                'attr' => ['data-action' => 'change->ec--step2#changeLangue', 'data-ec--step2-type-param' => 'langueSupport'],
                'class' => Langue::class,
                'choice_label' => 'libelle',
                'expanded' => true,
                'multiple' => true,
                'required' => true,
                'help' => '-'
            ])
            ->add('typeEnseignement', EntityType::class, [
                'attr' => ['data-action' => 'change->ec--step2#changeTypeEnseignement'],
                'class' => TypeEnseignement::class,
                'choice_label' => 'libelle',
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'help' => '-'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElementConstitutif::class,
            'translation_domain' => 'form'
        ]);
    }
}
