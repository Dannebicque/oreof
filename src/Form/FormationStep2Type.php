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
        $formation = $options['data'];

        $builder
            ->add('contenuFormation', TextareaAutoSaveType::class, [
                'label' => 'Contenu de la formation',
                'help' => 'Indiquez en 3000 caractères maximum le contenu de la formation et une description détaillée des différents sujets traités dans la formation.',
                'attr' => ['rows' => 20, 'maxlength' => 3000, 'data-action' => 'change->formation--step2#saveContenu'],
            ])
            ->add('resultatsAttendus', TextareaAutoSaveType::class, [
                'label' => 'Résultats attendus de la formation',
                'help' => 'Indiquez en 3000 caractères maximum les résultats attendus de la formation (titre, diplôme, certificat, attestation, …) et précise les modalités de reconnaissance ou de validation. De la même manière que pour l’élément “intitule-formation”, les diplômes, titres ou certifications devraient utiliser des dénominations conformes aux tables de l’Éducation Nationale ou au Répertoire National des Certifications Professionnelles (RNCP).',
                'attr' => ['rows' => 20, 'maxlength' => 3000, 'data-action' => 'click->formation--step2#saveResultats'],
            ])
            ->add('rythmeFormation', EntityType::class, [
                'label' => 'Rythme de la formation',
                'help' => 'Indiquez le rythme de la formation (temps plein, temps partiel, cours du soir,etc.).',
                'class' => RythmeFormation::class,
                'choice_label' => 'libelle',
                'attr' => ['data-action' => 'change->formation--step2#changeRythme'],
            ])
            ->add('rythmeFormationTexte', TextareaAutoSaveType::class, [
                'label' => 'Compléments sur le rythme de formation',
                'help' => 'Indiquez en 3000 caractères maximum le rythme de la formation : temps plein, temps partiel, cours du soir,etc..',
                'attr' => ['rows' => 20, 'maxlength' => 3000, 'data-action' => 'click->formation--step2#saveRythme'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
