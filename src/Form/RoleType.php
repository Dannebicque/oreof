<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/RoleType.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 15/02/2023 18:02
 */

namespace App\Form;

use App\Entity\Role;
use App\Enums\CentreGestionEnum;
use App\Form\Type\YesNoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle')
            ->add('code_role')
            ->add('porte', ChoiceType::class, [
                'choices' => [
                    'Tous les éléments' => 'ALL',
                    'Uniquement les éléments dont on a la propriété' => 'MY',
                ],
                'expanded' => true
            ])
            ->add('onlyAdmin', YesNoType::class, [
                'required' => true,
            ])
            ->add('centre', ChoiceType::class, [
                'choices' => [
                    'Formation' => CentreGestionEnum::CENTRE_GESTION_FORMATION,
                    'Composante' => CentreGestionEnum::CENTRE_GESTION_COMPOSANTE,
                    'Etablissement' => CentreGestionEnum::CENTRE_GESTION_ETABLISSEMENT,
                ],
                'placeholder' => 'Indiquez un centre de gestion',
                'required' => true,
                'expanded' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Role::class,
            'translation_domain' => 'form'
        ]);
    }
}
