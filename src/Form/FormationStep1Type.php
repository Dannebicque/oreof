<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/FormationStep1Type.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Form;

use App\Entity\Composante;
use App\Entity\Formation;
use App\Entity\Ville;
use App\Enums\RegimeInscriptionEnum;
use App\Form\Type\TextareaAutoSaveType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationStep1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('localisationMention', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => 'libelle',
                'multiple' => true,
                'expanded' => true,
                'required' => true,
                'help' => 'Plusieurs choix possibles',
                'choice_attr' => function () {
                    return ['data-action' => 'change->formation--step1#changeVille'];
                },
            ])
            ->add('composantesInscription', EntityType::class, [
                'class' => Composante::class,
                'choice_label' => 'libelle',
                'help' => 'Plusieurs choix possibles',
                'multiple' => true,
                'expanded' => true,
                'choice_attr' => function () {
                    return ['data-action' => 'change->formation--step1#changeComposanteInscription'];
                },
                'attr' => ['data-action' => 'change->formation--step6#changeComposanteInscription']
            ])//todo: faire une liste avec un "+" pour ajouter une composante d'inscription et un "-" pour retirer...
            ->add('regimeInscription', EnumType::class, [
                'help' => 'Régime d\'inscription',
                'class' => RegimeInscriptionEnum::class,
                'translation_domain' => 'form',
                'multiple' => true,
                'expanded' => true,
                'attr' => ['data-action' => 'change->formation--step1#changeRegimeInscription']
            ])
            ->add('modalitesAlternance', TextareaAutoSaveType::class, [
                'help' => 'Indiquez en 3000 caractères maximum les périodes et leurs durées en centre ou en entreprise.',
                'attr' => [
                    'rows' => 10,
                    'maxlength' => 3000,
                    'data-action' => 'change->formation--step1#saveModalitesAlternance'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
            'translation_domain' => 'form'
        ]);
    }
}
