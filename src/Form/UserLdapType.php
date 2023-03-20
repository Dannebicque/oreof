<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/UserLdapType.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/02/2023 09:31
 */

namespace App\Form;

use App\Entity\User;
use App\Enums\CentreGestionEnum;
use App\Repository\RoleRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UnitEnum;

class UserLdapType extends AbstractType
{
    private array $choices;
    public function __construct(RoleRepository $roleRepository)
    {
        $this->choices = [];
        $roles = $roleRepository->findByAll();
        foreach ($roles as $role) {
            $this->choices[$role->getCodeRole()] = $role->getLibelle();
        }
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Adresse email',
                'help' => 'Adresse email URCA',
                'attr' => ['maxlength' => 255]
            ])
            ->add('centreDemande', EnumType::class, [
                'class' => CentreGestionEnum::class,
                'choice_label' => static function (UnitEnum $choice): string {
                    return $choice->libelle();
                },
                'placeholder' => 'Indiquez un centre de gestion',
                'required' => true,
                'mapped' => false,
                'attr' => ['data-action' => 'change->register#changeCentre']
            ])

            ->add('role', ChoiceType::class, [
                'choices' => $this->choices,
                'expanded' => true,
                'label' => 'Droits',
                'placeholder' => 'Indiquez les droits accordés',
                'required' => true,
                'mapped' => false,
            ])
            ->add('sendMail', CheckboxType::class, [
                'label' => 'Envoyer un email de conformation à l\'utilisateur',
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'form'
        ]);
    }
}
