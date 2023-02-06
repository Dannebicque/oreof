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
                'label' => 'Description',
                'attr' => ['data-action' => 'change->ec--step2#saveDescription', 'maxlength' => 3000, 'rows' => 20],
                'help' => 'Indiquez ici en 3000 caractères maximum le contenu de l’enseignement et une description détaillée des différents sujets traités dans cet enseignement.'
            ])
            ->add('langueDispense', EntityType::class, [
                'attr' => ['data-action' => 'change->ec--step2#changeLangue', 'data-ec--step2-type-param' => 'langueDispense' ],
                'class' => Langue::class,
                'choice_label' => 'libelle',
                'label' => 'Enseignement dispensé en : ',
                'expanded' => true,
                'multiple' => true,
                'required' => true,
            ])
            ->add('langueSupport', EntityType::class, [
                'attr' => ['data-action' => 'change->ec--step2#changeLangue', 'data-ec--step2-type-param' => 'langueSupport'],
                'class' => Langue::class,
                'choice_label' => 'libelle',
                'label' => 'Support de cours en : ',
                'expanded' => true,
                'multiple' => true,
                'required' => true,
            ])
            ->add('typeEnseignement', EntityType::class, [
                'attr' => ['data-action' => 'change->ec--step2#changeTypeEnseignement'],
                'class' => TypeEnseignement::class,
                'choice_label' => 'libelle',
                'label' => 'Enseignement Obligatoire / optionnel ?',
                'expanded' => true,
                'multiple' => false,
                'required' => true,
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
