<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/ComposanteType.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 22/02/2023 10:54
 */

namespace App\Form;

use App\Entity\Composante;
use App\Entity\User;
use App\Form\Type\EntityWithAddType;
use App\Form\Type\YesNoType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ComposanteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, [
                'label' => 'Libellé',
                'required' => true,
            ])
            ->add('sigle', TextType::class, [
                'label' => 'Sigle',
                'help' => 'Le sigle est la dénomination courte de la composante, s\'il existe.',
                'required' => false,
            ])
            ->add('inscriptionUniquement', YesNoType::class, [
                'label' => 'Composante d\'inscription et où se déroulent les cours',
            ])
            ->add('composanteParent', EntityType::class, [
                'class' => Composante::class,
                'query_builder' => static function ($er) {
                    return $er->createQueryBuilder('t')
                        ->where('t.inscriptionUniquement = :inscription')
                        ->orWhere('t.inscriptionUniquement IS NULL')
                        ->setParameter('inscription', false)
                        ->orderBy('t.libelle', 'ASC');
                },
                'choice_label' => 'libelle',
                'autocomplete' => true,
                'required' => false,
            ])
            ->add('codeComposante', TextType::class, [
                'label' => 'Code Composante',
                'attr' => ['maxlength' => 3],
                'required' => true,
            ])
            ->add('codeApogee', TextType::class, [
                'label' => 'Code CIP (Apogée)',
                'attr' => ['maxlength' => 2],
                'required' => true,
            ])
            ->add('directeur', EntityWithAddType::class, [
                'class' => User::class,
                'choice_label' => 'display',
                'multiple' => false,
                'autocomplete' => true,
                'expanded' => false,
                'query_builder' => static function ($er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.nom', 'ASC')
                        ->addOrderBy('t.prenom', 'ASC');
                },
                'help_to_add' => 'Saisir l\'email urca de la personne à ajouter.',
                'placeholder' => 'Choisir dans la liste ou choisir "+" pour ajouter un nouveau directeur',
                'label' => 'Directeur de composante',
                'required' => false,
            ])
            ->add('responsableDpe', EntityWithAddType::class, [
                'class' => User::class,
                'choice_label' => 'display',
                'multiple' => false,
                'autocomplete' => true,
                'expanded' => false,
                'query_builder' => static function ($er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.nom', 'ASC')
                        ->addOrderBy('t.prenom', 'ASC');
                },
                'empty_data' => 'null',
                'help_to_add' => 'Saisir l\'email urca de la personne à ajouter.',
                'placeholder' => 'Choisir dans la liste ou choisir "+" pour ajouter un nouveau responsable',
                'label' => 'Responsable "DPE" de la composante',
                'help' => 'Personne responsable de la validation des DPE des formations de la composante',
                'required' => false,
            ])
            ->add('adresse', AdresseType::class, [
                'label' => 'Adresse du site principal de la composante',
            ])
            ->add('telStandard', TextType::class, [
                'label' => 'Téléphone standard de la composante',
                'required' => false,
                'attr' => ['maxlength' => 10]
            ])
            ->add('telComplementaire', TextType::class, [
                'label' => 'Autre téléphone de la composante',
                'required' => false,
                'attr' => ['maxlength' => 10]
            ])
            ->add('mailContact', TextType::class, [
                'label' => 'Adresse mail de contact de la composante',
                'required' => false,
                'attr' => ['maxlength' => 255]
            ])
            ->add('urlSite', TextType::class, [
                'label' => 'URL du site de la composante',
                'required' => false,
                'attr' => ['maxlength' => 255]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Composante::class,
            'translation_domain' => 'form'
        ]);
    }
}
