<?php

namespace App\Form;

use App\Entity\Parcours;
use App\Entity\User;
use App\Form\Type\TextareaAutoSaveType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParcoursStep8Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('respParcours', EntityType::class, [
                'label' => 'Responsable du parcours (si existant)',
                'required' => false,
                'class' => User::class,
                'choice_label' => 'display',
                'attr' => ['data-action' => 'change->parcours--step8#respParcours'],
            ])
            ->add('coordSecretariat', TextareaAutoSaveType::class, [
                'label' => 'Coordonnées du secrétariat',
                'attr' => ['rows' => 5, 'maxlength' => 3000, 'data-action' => 'change->parcours--step8#coordSecretariat'],
                'help' => 'Indiquez ici les coordonnées postales et téléphoniques du secrétariat ainsi que son adresse mail générique.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Parcours::class,
        ]);
    }
}
