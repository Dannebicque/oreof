<?php

namespace App\Form;

use App\Entity\Formation;
use App\Entity\RythmeFormation;
use App\Form\Type\TextareaAutoSaveType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationStep2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('objectifsFormation', TextareaAutoSaveType::class, [
                'help' => '-',
                'attr' => ['rows' => 10, 'maxlength' => 3000, 'data-action' => 'change->formation--step2#saveObjectifsFormation'],
            ])
            ->add('resultatsAttendus', TextareaAutoSaveType::class, [
                'help' => '-',
                'attr' => ['rows' => 10, 'maxlength' => 3000, 'data-action' => 'change->formation--step2#saveResultats'],
            ])
            ->add('contenuFormation', TextareaAutoSaveType::class, [
                'help' => '-',
                'attr' => ['rows' => 20, 'maxlength' => 3000, 'data-action' => 'change->formation--step2#saveContenu'],
            ])
            ->add('rythmeFormation', EntityType::class, [
                'placeholder' => 'Choisissez un rythme de formation ou complétez le champ ci-dessous',
                'required' => false,
                'help' => '-',
                'class' => RythmeFormation::class,
                'choice_label' => 'libelle',
                'attr' => ['data-action' => 'change->formation--step2#changeRythme'],
            ])


            ->add('rythmeFormationTexte', TextareaAutoSaveType::class, [
                'required' => false,
                'help' => '-',
                'attr' => ['rows' => 10, 'maxlength' => 3000, 'data-action' => 'change->formation--step2#saveRythme'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
            'translation_domain' => 'form'
        ]);
    }
}
