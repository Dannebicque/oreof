<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/ParcoursStep1Type.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Form;

use App\Entity\Parcours;
use App\Entity\RythmeFormation;
use App\Entity\User;
use App\Entity\Ville;
use App\Form\Type\EntityWithAddType;
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

//        $villes = [];
//        foreach ($formation->getLocalisationMention() as $ville) {
//            $villes[$ville->getLibelle()] = $ville->getId();
//        }

        $builder
            ->add('respParcours', EntityWithAddType::class, [
                'required' => true,
                'help' => '',
                'class' => User::class,
                'autocomplete' => true,
                'choice_label' => 'display',
                'query_builder' => function ($er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.nom', 'ASC')
                        ->addOrderBy('u.prenom', 'ASC');
                },
                'help_to_add' => 'Saisir l\'email urca de la personne à ajouter.',
                'placeholder' => 'Choisir dans la liste ou choisir "+" pour ajouter un utilisateur',
            ])
            ->add('coResponsable', EntityWithAddType::class, [
                'required' => false,
                'help' => '',
                'autocomplete' => true,
                'class' => User::class,
                'choice_label' => 'display',
                'query_builder' => function ($er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.nom', 'ASC')
                        ->addOrderBy('u.prenom', 'ASC');
                },
                'help_to_add' => 'Saisir l\'email urca de la personne à ajouter.',
                'placeholder' => 'Choisir dans la liste ou choisir "+" pour ajouter un utilisateur',
            ])
            ->add('objectifsParcours', TextareaAutoSaveType::class, [
                'help' => '-',
                'attr' => [
                    'rows' => 10,
                    'maxlength' => 3000,
                ],
            ])
            ->add('motsCles', TextareaAutoSaveType::class, [
                'help' => '-',
                'attr' => [
                    'rows' => 10,
                    'maxlength' => 3000,
                ],
            ])
            ->add('resultatsAttendus', TextareaAutoSaveType::class, [
                'help' => '-',
                'attr' => ['rows' => 10, 'maxlength' => 3000,],
            ])
            ->add('contenuFormation', TextareaAutoSaveType::class, [
                'help' => '-',
                'attr' => ['rows' => 20, 'maxlength' => 3000,],
            ])
            ->add('rythmeFormation', EntityType::class, [
                'placeholder' => 'Choisissez un rythme de formation ou complétez le champ ci-dessous',
                'required' => true,
                'help' => '-',
                'class' => RythmeFormation::class,
                'choice_label' => 'libelle',
            ])
            ->add('rythmeFormationTexte', TextareaAutoSaveType::class, [
                'required' => false,
                'help' => '-',
                'attr' => ['rows' => 10, 'maxlength' => 3000],
            ])
            ->add('localisation', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => 'libelle',
                'placeholder' => 'Choisissez une ville',
                'expanded' => false,
                'choices' => $formation->getLocalisationMention()->toArray(),
                'data' => $options['data']->getLocalisation(),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Parcours::class,
            'translation_domain' => 'form'
        ]);
    }
}
