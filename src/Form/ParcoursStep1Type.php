<?php

namespace App\Form;

use App\Entity\Parcours;
use App\Entity\RythmeFormation;
use App\Form\Type\TextareaWithSaveType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParcoursStep1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $formation = $options['data'];

        $builder
            ->add('contenuFormation', TextareaWithSaveType::class, [
                'label' => 'Contenu du parcours',
                'help' => 'Indiquez en 3000 caractères maximum le contenu de la formation et une description détaillée des différents sujets traités dans la formation.',
                'attr' => ['rows' => 20, 'maxlength' => 3000],
                'button_action' => 'click->parcours--step1#saveContenu',
            ])
            ->add('resultatsAttendus', TextareaWithSaveType::class, [
                'label' => 'Résultats attendus du parcours',
                'help' => 'Indiquez en 3000 caractères maximum les résultats attendus de la formation (titre, diplôme, certificat, attestation, …) et précise les modalités de reconnaissance ou de validation. De la même manière que pour l’élément “intitule-formation”, les diplômes, titres ou certifications devraient utiliser des dénominations conformes aux tables de l’Éducation Nationale ou au Répertoire National des Certifications Professionnelles (RNCP).',
                'attr' => ['rows' => 20, 'maxlength' => 3000],
                'button_action' => 'click->parcours--step1#saveResultats',
            ])
            ->add('rythmeFormation', EntityType::class, [
                'label' => 'Rythme du parcours',
                'help' => 'Indiquez le rythme de la formation (en heures, en jours, en semaines, en mois, en années, …).',
                'class' => RythmeFormation::class,
                'choice_label' => 'libelle',
                'attr' => ['data-action' => 'change->parcours--step1#changeRythme'],
            ])
            ->add('rythmeFormationTexte', TextareaWithSaveType::class, [
                'label' => 'Résultats attendus de la formation',
                'help' => 'Indiquez en 3000 caractères maximum le rythme de la formation : temps plein, temps partiel, cours du soir,etc..',
                'attr' => ['rows' => 20, 'maxlength' => 3000],
                'button_action' => 'click->parcours--step1#saveRythme',
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Parcours::class,
        ]);
    }
}
