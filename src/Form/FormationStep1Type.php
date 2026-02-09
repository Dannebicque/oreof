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
use App\Entity\User;
use App\Entity\Ville;
use App\Enums\RegimeInscriptionEnum;
use App\Form\Type\TextareaAutoSaveType;
use App\Repository\ComposanteRepository;
use App\Repository\VilleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationStep1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('responsableMention', EntityType::class, [
                'required' => false,
                'disabled' => true,
                'class' => User::class,
                'choice_label' => 'display',
            ])
            ->add('coResponsable', EntityType::class, [
                'required' => false,
                'disabled' => true,
                'class' => User::class,
                'choice_label' => 'display',
            ])
            ->add('sigle', TextType::class, [
                'required' => false,
                'attr' => ['data-action' => 'change->formation--step1#changeSigle', 'maxlength' => 250],
            ])
            ->add('localisationMention', EntityType::class, [
                'class' => Ville::class,
                'query_builder' => function (VilleRepository $villeRepository) {
                    return $villeRepository->createQueryBuilder('v')
                        ->orderBy('v.libelle', 'ASC');
                },
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
                'query_builder' => function (ComposanteRepository $composanteRepository) {
                    return $composanteRepository->createQueryBuilder('comp')
                        ->orderBy('comp.libelle', 'ASC');
                },
//                'choice_attr' => function () {
//                    return ['data-action' => 'change->formation--step1#changeComposanteInscription'];
//                },
                'attr' => [
                    'columns' => 2,
//                    'data-action' => 'change->formation--step6#changeComposanteInscription'
                ],
            ])
            ->add('regimeInscription', EnumType::class, [
                'help' => 'Régime d\'inscription',
                'class' => RegimeInscriptionEnum::class,
                'translation_domain' => 'form',
                'multiple' => true,
                'expanded' => true,
                //'attr' => ['data-action' => 'change->formation--step1#changeRegimeInscription']
                'choice_attr' => function ($choice) {
                    // On marque chaque checkbox comme une cible 'trigger'
                    return [
                        'data-conditional-field-target' => 'trigger',
                        'data-action' => 'change->conditional-field#toggle'
                    ];
                },
//                'attr' => [
//                    'data-controller' => 'conditional-display',
//                    'data-conditional-display-expected-values-value' => json_encode(['FI_APPRENTISSAGE', 'FC_CONTRAT_PRO'])
//                ]
            ])
            ->add('modalitesAlternance', TextareaAutoSaveType::class, [
                'help' => 'Indiquez en 3000 caractères maximum les périodes et leurs durées en centre ou en entreprise.',
                'attr' => [
                    'rows' => 10,
                    'maxlength' => 3000,
                    //'data-action' => 'change->formation--step1#saveModalitesAlternance'
                ],
                'row_attr' => [
//                    'data-conditional-display-target' => 'container',
                    'class' => 'd-none' // Sera retiré par le connect() si déjà coché
                ]
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
