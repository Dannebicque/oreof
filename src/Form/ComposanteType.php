<?php

namespace App\Form;

use App\Entity\Composante;
use App\Entity\User;
use App\Form\Type\EntityWithAddType;
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
            ->add('directeur', EntityWithAddType::class, [
                'class' => User::class,
                'choice_label' => 'display',
                'multiple' => false,
                'expanded' => false,
                'help_to_add' => 'Saisir l\'email urca de la personne à ajouter.',
                'placeholder' => 'Choisir dans la liste ou choisir "+" pour ajouter un nouveau directeur',
                'label' => 'Directeur de composante',
                'required' => false,
            ])
            ->add('responsableDpe', EntityWithAddType::class, [
                'class' => User::class,
                'choice_label' => 'display',
                'multiple' => false,
                'expanded' => false,
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
        ]);
    }
}
