<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/FicheMatiereStep1Type.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Form;

use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\User;
use App\Form\Type\YesNoType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class FicheMatiereStep1Type extends AbstractType
{
    private ?AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
//        $access = $this->authorizationChecker->isGranted(
//            'ROLE_FORMATION_EDIT_MY',
//            $options['data']->getParcours()->getFormation()
//        );
        $access = true;
        $builder
            ->add('responsableFicheMatiere', EntityType::class, [
                'class' => User::class,
'autocomplete' => true,
//                'query_builder' => function ($er) use ($options) {
//                    return $er->createQueryBuilder('u')
//                        ->join('u.roles', 'r')
//                        ->where('r.role = :role')
//                        ->andWhere('u.parcours = :parcours')
//                        ->setParameter('role', 'ROLE_FORMATION_EDIT_MY')
//                        ->setParameter('parcours', $options['data']->getParcours()->getId())
//                        ->orderBy('u.nom', 'ASC')
//                        ->addOrderBy('u.prenom', 'ASC');
//                },//todo: filtrer par user dans le centre
                'disabled' => !$access,
                'attr' => ['data-action' => 'change->fichematiere--step1#changeResponsableEc'],
                'choice_label' => 'display',
            ])
            ->add('libelle', TextType::class, [
                'disabled' => !$access,
                'attr' => ['data-action' => 'change->fichematiere--step1#saveContenuFr', 'maxlength' => 250],
            ])
            ->add('libelleAnglais', TextType::class, [
                'attr' => ['data-action' => 'change->fichematiere--step1#saveContenuEn', 'maxlength' => 250],
            ])
            ->add('enseignementMutualise', YesNoType::class, [
                'attr' => ['data-action' => 'change->fichematiere--step1#changeEnseignementMutualise'],
            ])
            ->add('isCmPresentielMutualise', YesNoType::class, [
                'attr' => [
                    'data-action' => 'change->fichematiere--step1#isMutualise',
                    'data-fichematiere--step1-type-param' => 'isCmPresentielMutualise'
                ],
            ])
            ->add('isTdPresentielMutualise', YesNoType::class, [
                'attr' => [
                    'data-action' => 'change->fichematiere--step1#isMutualise',
                    'data-fichematiere--step1-type-param' => 'isTdPresentielMutualise'
                ],
            ])
            ->add('isTpPresentielMutualise', YesNoType::class, [
                'attr' => [
                    'data-action' => 'change->fichematiere--step1#isMutualise',
                    'data-fichematiere--step1-type-param' => 'isTpPresentielMutualise'
                ],
            ])
            ->add('isCmDistancielMutualise', YesNoType::class, [
                'attr' => [
                    'data-action' => 'change->fichematiere--step1#isMutualise',
                    'data-fichematiere--step1-type-param' => 'isCmDistancielMutualise'
                ],
                'help' => '-'
            ])
            ->add('isTdDistancielMutualise', YesNoType::class, [
                'attr' => [
                    'data-action' => 'change->fichematiere--step1#isMutualise',
                    'data-fichematiere--step1-type-param' => 'isTdDistancielMutualise'
                ],
                'help' => '-'
            ])
            ->add('isTpDistancielMutualise', YesNoType::class, [
                'attr' => [
                    'data-action' => 'change->fichematiere--step1#isMutualise',
                    'data-fichematiere--step1-type-param' => 'isTpDistancielMutualise'
                ],
                'help' => '-'
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FicheMatiere::class,
            'translation_domain' => 'form'
        ]);
    }
}
