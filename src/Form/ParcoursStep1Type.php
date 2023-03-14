<?php

namespace App\Form;

use App\Entity\Parcours;
use App\Entity\RythmeFormation;
use App\Form\Type\TextareaAutoSaveType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParcoursStep1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $formation = $options['data']->getFormation();

        $villes = [];
        foreach ($formation->getLocalisationMention() as $ville) {
            $villes[$ville->getLibelle()] = $ville->getId();
        }

        $builder
            ->add('objectifsParcours', TextareaAutoSaveType::class, [
                'help' => '-',
                'attr' => ['rows' => 10, 'maxlength' => 3000, 'data-action' => 'change->parcours--step1#saveObjectifsParcours'],
            ])
            ->add('resultatsAttendus', TextareaAutoSaveType::class, [
                'help' => '-',
                'attr' => ['rows' => 10, 'maxlength' => 3000, 'data-action' => 'change->parcours--step1#saveResultats'],
            ])
            ->add('contenuFormation', TextareaAutoSaveType::class, [
                'help' => '-',
                'attr' => ['rows' => 20, 'maxlength' => 3000, 'data-action' => 'change->parcours--step1#saveContenu'],
            ])

            ->add('rythmeFormation', EntityType::class, [
                'placeholder' => 'Choisissez un rythme de formation ou complétez le champ ci-dessous',
                'required' => false,
                'help' => '-',
                'class' => RythmeFormation::class,
                'choice_label' => 'libelle',
                'attr' => ['data-action' => 'change->parcours--step1#changeRythme'],
            ])
            ->add('rythmeFormationTexte', TextareaAutoSaveType::class, [
                'required' => false,
                'help' => '-',
                'attr' => ['rows' => 10, 'maxlength' => 3000, 'data-action' => 'change->parcours--step1#saveRythme'],
            ])
            ->add('localisation', ChoiceType::class, [
                'required' => false,
                'expanded' => true,
                'choices' => $villes,
                'data' => $options['data']->getLocalisation()?->getId() ?? 0,
                'help' => '-',
                'attr' => ['data-action' => 'change->parcours--step1#changeLocalisation'],
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Parcours::class,
            'translation_domain' => 'form'
        ]);
    }
}
