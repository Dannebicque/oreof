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
                'label' => 'Poursuites d\'études envisageables',
                'attr' => ['rows' => 20, 'maxlength' => 3000],
                'help' => 'Indiquez en 3000 caractères maximum quelles sont les poursuites d’études envisageables.',
                'button_action' => 'click->parcours--step7#savePoursuitesEtudes',
            ])
            ->add('debouches', TextareaWithSaveType::class, [
                'label' => 'Débouchés',
                'attr' => ['rows' => 10, 'maxlength' => 3000],
                'help' => 'Indiquez ici les principaux débouchés professionnels accessibles à l’issue de cette formation.',
                'button_action' => 'click->parcours--step7#saveDebouches',
            ])
            ->add('code', TextType::class, [
                'label' => 'Code ROME',
                'mapped' => false,
                'help' => 'Indiquez le code ROME accessible à l’issue de cette formation',
            ])
            ->add('libelleCode', TextType::class, [
                'label' => 'Libellé du code ROME',
                'mapped' => false,
                'help' => 'Indiquez le libellé du code ROME accessible à l’issue de cette formation',
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
