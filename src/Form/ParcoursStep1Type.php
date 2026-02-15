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
use App\Entity\TypeEc;
use App\Entity\User;
use App\Entity\Ville;
use App\Form\Type\EntityWithAddType;
use App\Form\Type\InlineCreateEntitySelectType;
use App\Form\Type\TextareaAutoSaveType;
use App\Repository\TypeEcRepository;
use Doctrine\ORM\EntityManagerInterface;
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

        $builder
            ->add('respParcours', InlineCreateEntitySelectType::class, [
                'help' => '',
                'class' => User::class,
                'choice_label' => 'display',
                'query_builder' => function ($er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.nom', 'ASC')
                        ->addOrderBy('u.prenom', 'ASC');
                },
                'placeholder' => 'Choisir dans la liste ou choisir "+ Créer Nouveau" pour ajouter un utilisateur',
                'new_placeholder' => 'Email du responsable du parcours',
                'required' => true,
                'label' => 'Responsable du parcours',
                'ldap_check' => true,

                // évite doublons (optionnel)
                'find_existing' => function (string $label, $scope, EntityManagerInterface $em) {
                    return $em->getRepository(User::class)->createQueryBuilder('t')
                        ->andWhere('LOWER(t.email) = LOWER(:l)')
                        ->setParameter('l', $label)
                        ->getQuery()
                        ->getOneOrNullResult();
                },

                // création (obligatoire)
                'create' => function (string $label, EntityManagerInterface $em) {
                    $e = new User();
                    $e->setEmail($label);
                    return $e; // persist/flush gérés par le type (ou tu peux le faire ici)
                },

            ])
            ->add('coResponsable', InlineCreateEntitySelectType::class, [
                'help' => '',
                'class' => User::class,
                'choice_label' => 'display',
                'query_builder' => function ($er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.nom', 'ASC')
                        ->addOrderBy('u.prenom', 'ASC');
                },
                'placeholder' => 'Choisir dans la liste ou choisir "+ Créer Nouveau" pour ajouter un utilisateur',
                'new_placeholder' => 'Email du co-responsable du parcours',
                'required' => true,
                'label' => 'Co-Responsable du parcours',
                'ldap_check' => true,

                // évite doublons (optionnel)
                'find_existing' => function (string $label, $scope, EntityManagerInterface $em) {
                    return $em->getRepository(User::class)->createQueryBuilder('t')
                        ->andWhere('LOWER(t.email) = LOWER(:l)')
                        ->setParameter('l', $label)
                        ->getQuery()
                        ->getOneOrNullResult();
                },

                // création (obligatoire)
                'create' => function (string $label, EntityManagerInterface $em) {
                    $e = new User();
                    $e->setEmail($label);
                    return $e; // persist/flush gérés par le type (ou tu peux le faire ici)
                },

            ])
//            ->add('coResponsable', EntityWithAddType::class, [
//                'required' => false,
//                'help' => '',
//                'autocomplete' => true,
//                'class' => User::class,
//                'choice_label' => 'display',
//                'query_builder' => function ($er) {
//                    return $er->createQueryBuilder('u')
//                        ->orderBy('u.nom', 'ASC')
//                        ->addOrderBy('u.prenom', 'ASC');
//                },
//                'help_to_add' => 'Saisir l\'email urca de la personne à ajouter.',
//                'placeholder' => 'Choisir dans la liste ou choisir "+" pour ajouter un utilisateur',
//            ])
            ->add('objectifsParcours', TextareaAutoSaveType::class, [
                'attr' => [
                    'rows' => 10,
                    'maxlength' => 3000,
                ],
            ])
            ->add('motsCles', TextareaAutoSaveType::class, [
                'attr' => [
                    'rows' => 10,
                    'maxlength' => 3000,
                ],
            ])
            ->add('resultatsAttendus', TextareaAutoSaveType::class, [
                'attr' => ['rows' => 10, 'maxlength' => 3000,],
            ])
            ->add('contenuFormation', TextareaAutoSaveType::class, [
                'attr' => ['rows' => 20, 'maxlength' => 3000,],
            ])
            ->add('rythmeFormation', EntityType::class, [
                'placeholder' => 'Choisissez un rythme de formation ou complétez le champ ci-dessous',
                'required' => true,
                'class' => RythmeFormation::class,
                'choice_label' => 'libelle',
            ])
            ->add('rythmeFormationTexte', TextareaAutoSaveType::class, [
                'required' => false,
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
