<?php

namespace App\Form;

use App\Entity\Composante;
use App\Entity\Parcours;
use App\Enums\RegimeInscriptionEnum;
use App\Form\Type\TextareaAutoSaveType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParcoursStep6Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $formation = $options['data'];

        $builder
            ->add('composanteInscription', EntityType::class, [
                'class' => Composante::class,
                'choice_label' => 'libelle',
                'label' => 'Composante d\'inscription',
                'multiple' => false,
                'expanded' => true,
                'choice_attr' => function($choice, $key, $value) {
                    return ['data-action' => 'change->parcours--step6#changeComposanteInscription'];
                },
            ])//todo: faire une liste avec un "+" pour ajouter une composante d'inscription et un "-" pour retirer...
            ->add('regimeInscription', EnumType::class, [
                'label' => 'Régime d\'inscription',
                'class' => RegimeInscriptionEnum::class,
                'translation_domain' => 'enum',
                'multiple' => true,
                'expanded' => true,
                'attr' => ['data-action' => 'change->parcours--step6#changeRegimeInscription']
            ])
            ->add('modalitesAlternance', TextareaAutoSaveType::class, [
                'label' => 'Modalités de l\'alternance',
                'help' => 'Indiquez en 3000 caractères maximum les périodes et leurs durées en centre ou en entreprise.',
                'attr' => ['rows' => 20, 'maxlength' => 3000, 'data-action' => 'change->parcours--step6#saveModalitesAlternance'],
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
