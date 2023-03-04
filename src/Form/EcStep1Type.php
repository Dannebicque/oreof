<?php

namespace App\Form;

use App\Entity\ElementConstitutif;
use App\Entity\User;
use App\Form\Type\TextareaAutoSaveType;
use App\Form\Type\YesNoType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EcStep1Type extends AbstractType
{
    private ?AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $access = $this->authorizationChecker->isGranted('ROLE_FORMATION_EDIT_MY',
            $options['data']->getParcours()->getFormation());
        $builder
            ->add('responsableEc', EntityType::class, [
//                'label' => 'Responsable de l\'EC',
                'help' => 'Responsable de l\'EC',
                'class' => User::class,
                'disabled' => !$access,
                'attr' => ['data-action' => 'change->ec--step1#changeResponsableEc'],
                'choice_label' => 'display',
            ])
            ->add('libelle', TextareaAutoSaveType::class, [
//                'label' => 'Libellé',
                'disabled' => !$access,
                'attr' => ['data-action' => 'change->ec--step1#saveContenuFr', 'maxlength' => 250],
                'help' => 'N\'est modifiable que par le responsable de la formation',
            ])
            ->add('libelleAnglais', TextareaAutoSaveType::class, [
//                'label' => 'Libellé anglais',
                'attr' => ['data-action' => 'change->ec--step1#saveContenuEn', 'maxlength' => 250],
                'help' => 'Précisez la version anglaise du libellé de l\'EC',
            ])
            ->add('enseignementMutualise', YesNoType::class, [
                'attr' => ['data-action' => 'change->ec--step1#changeEnseignementMutualise'],
//                'label' => 'Enseignement mutualisé ?',
                'help' => 'Si une partie de la formation est mutualisée (CM par exemple), mais une autre est différenciée (TP par exemple), cochez cette case.',
            ])
            ->add('isCmPresentielMutualise', YesNoType::class, [
                'attr' => [
                    'data-action' => 'change->ec--step1#isMutualise',
                    'data-ec--step1-type-param' => 'isCmPresentielMutualise'
                ],
//                'label' => 'Les CM présentiels sont-ils mutualisés',
            ])
            ->add('isTdPresentielMutualise', YesNoType::class, [
                'attr' => [
                    'data-action' => 'change->ec--step1#isMutualise',
                    'data-ec--step1-type-param' => 'isTdPresentielMutualise'
                ],
//                'label' => 'Les TD présentiels sont-ils mutualisés',
            ])
            ->add('isTpPresentielMutualise', YesNoType::class, [
                'attr' => [
                    'data-action' => 'change->ec--step1#isMutualise',
                    'data-ec--step1-type-param' => 'isTpPresentielMutualise'
                ],
//                'label' => 'Les TP présentiels sont-ils mutualisés',
            ])
            ->add('isCmDistancielMutualise', YesNoType::class, [
                'attr' => [
                    'data-action' => 'change->ec--step1#isMutualise',
                    'data-ec--step1-type-param' => 'isCmDistancielMutualise'
                ],
//                'label' => 'Les CM distanciels sont-ils mutualisés',
            ])
            ->add('isTdDistancielMutualise', YesNoType::class, [
                'attr' => [
                    'data-action' => 'change->ec--step1#isMutualise',
                    'data-ec--step1-type-param' => 'isTdDistancielMutualise'
                ],
//                'label' => 'Les TD distanciels sont-ils mutualisés',
            ])
            ->add('isTpDistancielMutualise', YesNoType::class, [
                'attr' => [
                    'data-action' => 'change->ec--step1#isMutualise',
                    'data-ec--step1-type-param' => 'isTpDistancielMutualise'
                ],
//                'label' => 'Les TP distanciels sont-ils mutualisés',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElementConstitutif::class,
        ]);
    }
}
