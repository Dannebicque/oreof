<?php

namespace App\Form;

use App\Entity\Composante;
use App\Entity\Formation;
use App\Entity\Site;
use App\Enums\RegimeInscriptionEnum;
use App\Form\Type\TextareaWithSaveType;
use App\Form\Type\YesNoType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationStep6Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('composanteInscription', EntityType::class, [
                'class' => Composante::class,
                'choice_label' => 'libelle',
                'label' => 'Composante d\'inscription',
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
                'attr' => ['data-action' => 'change->formation--step6#changeComposanteInscription']
            ])//todo: faire une liste avec un "+" pour ajouter une composante d'inscription et un "-" pour retirer...
            ->add('regimeInscription', EnumType::class, [
                'label' => 'Régime d\'inscription',
                'class' => RegimeInscriptionEnum::class,
                'mapped' => false,
                'multiple' => true,
                'expanded' => true,
                'attr' => ['data-action' => 'change->formation--step6#changeRegimeInscription']
            ])
            ->add('modaliteAlternance', TextareaWithSaveType::class, [
                'label' => 'Modalités de l\'alternance',
                'help' => 'Indiquez en 3000 caractères maximum les périodes et leurs durées en centre ou en entreprise.',
                'attr' => ['row' => 20, 'maxlength' => 3000],
                'mapped' => false,
                'button_action' => 'click->formation--step6#saveModaliteAlternance',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
