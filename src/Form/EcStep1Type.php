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
                'help' => '-',
                'class' => User::class,
//                'query_builder' => function ($er) use ($options) {
//                    return $er->createQueryBuilder('u')
//                        ->join('u.roles', 'r')
//                        ->where('r.role = :role')
//                        ->andWhere('u.parcours = :parcours')
//                        ->setParameter('role', 'ROLE_FORMATION_EDIT_MY')
//                        ->setParameter('parcours', $options['data']->getParcours()->getId())
//                        ->orderBy('u.nom', 'ASC')
//                        ->addOrderBy('u.prenom', 'ASC');
//                },//filtrer par user dans le centre
                'disabled' => !$access,
                'attr' => ['data-action' => 'change->ec--step1#changeResponsableEc'],
                'choice_label' => 'display',
            ])
            ->add('libelle', TextareaAutoSaveType::class, [
                'disabled' => !$access,
                'attr' => ['data-action' => 'change->ec--step1#saveContenuFr', 'maxlength' => 250],
                'help' => '-',
            ])
            ->add('libelleAnglais', TextareaAutoSaveType::class, [
                'attr' => ['data-action' => 'change->ec--step1#saveContenuEn', 'maxlength' => 250],
                'help' => '-',
            ])
            ->add('enseignementMutualise', YesNoType::class, [
                'attr' => ['data-action' => 'change->ec--step1#changeEnseignementMutualise'],
                'help' => '-',
            ])
            ->add('isCmPresentielMutualise', YesNoType::class, [
                'attr' => [
                    'data-action' => 'change->ec--step1#isMutualise',
                    'data-ec--step1-type-param' => 'isCmPresentielMutualise'
                ],
                'help' => '-'
            ])
            ->add('isTdPresentielMutualise', YesNoType::class, [
                'attr' => [
                    'data-action' => 'change->ec--step1#isMutualise',
                    'data-ec--step1-type-param' => 'isTdPresentielMutualise'
                ],
                'help' => '-'
            ])
            ->add('isTpPresentielMutualise', YesNoType::class, [
                'attr' => [
                    'data-action' => 'change->ec--step1#isMutualise',
                    'data-ec--step1-type-param' => 'isTpPresentielMutualise'
                ],
                'help' => '-'
            ])
            ->add('isCmDistancielMutualise', YesNoType::class, [
                'attr' => [
                    'data-action' => 'change->ec--step1#isMutualise',
                    'data-ec--step1-type-param' => 'isCmDistancielMutualise'
                ],
                'help' => '-'
            ])
            ->add('isTdDistancielMutualise', YesNoType::class, [
                'attr' => [
                    'data-action' => 'change->ec--step1#isMutualise',
                    'data-ec--step1-type-param' => 'isTdDistancielMutualise'
                ],
                'help' => '-'
            ])
            ->add('isTpDistancielMutualise', YesNoType::class, [
                'attr' => [
                    'data-action' => 'change->ec--step1#isMutualise',
                    'data-ec--step1-type-param' => 'isTpDistancielMutualise'
                ],
                'help' => '-'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElementConstitutif::class,
            'translation_domain' => 'form'
        ]);
    }
}
