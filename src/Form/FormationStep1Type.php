<?php

namespace App\Form;

use App\Entity\Composante;
use App\Entity\Formation;
use App\Entity\Ville;
use App\Enums\RegimeInscriptionEnum;
use App\Form\Type\TextareaWithSaveType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationStep1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $formation = $options['data'];

        $builder
            ->add('localisationMention', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => 'libelle',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Localisation(s) de la formation',
                'help' => 'Plusieurs choix possibles',
                'choice_attr' => function($choice, $key, $value) {
                    return ['data-action' => 'change->formation--step1#changeVille'];
                },
            ])
            ->add('composantesInscription', EntityType::class, [
                'class' => Composante::class,
                'choice_label' => 'libelle',
                'label' => 'Composante d\'inscription',
                'multiple' => true,
                'expanded' => true,
                'choice_attr' => function($choice, $key, $value) {
                    return ['data-action' => 'change->formation--step1#changeComposanteInscription'];
                },
                'attr' => ['data-action' => 'change->formation--step6#changeComposanteInscription']
            ])//todo: faire une liste avec un "+" pour ajouter une composante d'inscription et un "-" pour retirer...
            ->add('regimeInscription', EnumType::class, [
                'label' => 'Régime d\'inscription',
                'class' => RegimeInscriptionEnum::class,
                'translation_domain' => 'enum',
                'multiple' => true,
                'expanded' => true,
                'attr' => ['data-action' => 'change->formation--step1#changeRegimeInscription']
            ])
            ->add('modalitesAlternance', TextareaWithSaveType::class, [
                'label' => 'Modalités de l\'alternance',
                'help' => 'Indiquez en 3000 caractères maximum les périodes et leurs durées en centre ou en entreprise.',
                'attr' => ['rows' => 20, 'maxlength' => 3000],
                'button_action' => 'click->formation--step1#saveModalitesAlternance',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
