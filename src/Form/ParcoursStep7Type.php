<?php

namespace App\Form;

use App\Entity\Parcours;
use App\Form\Type\TextareaWithSaveType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParcoursStep7Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('poursuitesEtudes', TextareaWithSaveType::class, [
                'label' => 'Responsable du parcours (si existant)',
                'required' => false,
                'button_action' => 'click->parcours--step7#savePoursuitesEtudes',
            ])
            ->add('debouches', TextareaWithSaveType::class, [
                'label' => 'Coordonnées du secrétariat',
                'attr' => ['rows' => 5, 'maxlength' => 3000],
                'help' => 'Indiquez ici les coordonnées postales et téléphoniques du secrétariat ainsi que son adresse mail générique.',
                'button_action' => 'click->parcours--step5#saveDebouches',
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
