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
            ->add('contenuFormation', TextareaAutoSaveType::class, [
                'label' => 'Contenu du parcours',
                'help' => 'Indiquez en 3000 caractères maximum le contenu de la formation et une description détaillée des différents sujets traités dans la formation.',
                'attr' => ['rows' => 20, 'maxlength' => 3000, 'data-action' => 'click->parcours--step1#saveContenu'],
            ])
            ->add('resultatsAttendus', TextareaAutoSaveType::class, [
                'label' => 'Résultats attendus du parcours',
                'help' => 'Indiquez en 3000 caractères maximum les résultats attendus de la formation (titre, diplôme, certificat, attestation, …) et précise les modalités de reconnaissance ou de validation. De la même manière que pour l’élément “intitule-formation”, les diplômes, titres ou certifications devraient utiliser des dénominations conformes aux tables de l’Éducation Nationale ou au Répertoire National des Certifications Professionnelles (RNCP).',
                'attr' => ['rows' => 20, 'maxlength' => 3000, 'data-action' => 'click->parcours--step1#saveResultats'],
            ])
            ->add('rythmeFormation', EntityType::class, [
                'placeholder' => 'Choisissez un rythme de formation ou complétez le champ ci-dessous',
                'required' => false,
                'label' => 'Rythme du parcours',
                'help' => 'Indiquez le rythme du parcours (en heures, en jours, en semaines, en mois, en années, …).',
                'class' => RythmeFormation::class,
                'choice_label' => 'libelle',
                'attr' => ['data-action' => 'change->parcours--step1#changeRythme'],
            ])
            ->add('rythmeFormationTexte', TextareaAutoSaveType::class, [
                'required' => false,
                'label' => 'Résultats attendus de la formation',
                'help' => 'Indiquez en 3000 caractères maximum le rythme de la formation : temps plein, temps partiel, cours du soir,etc..',
                'attr' => ['rows' => 20, 'maxlength' => 3000, 'data-action' => 'click->parcours--step1#saveRythme'],
            ])
            ->add('localisation', ChoiceType::class, [
                'required' => false,
                'expanded' => true,
                'label' => 'Localisation du parcours',
                'choices' => $villes,
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
