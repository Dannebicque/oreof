<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/TypeUeType.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 11/02/2023 07:54
 */

namespace App\Form;

use App\Entity\TypeDiplome;
use App\Entity\TypeUe;
use App\Enums\TypeUeEcEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeUeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, [
                'label' => 'Libellé',
            ])
            ->add('type', EnumType::class, [
                'help' => 'Type UE',
                'class' => TypeUeEcEnum::class,
                'translation_domain' => 'form',
                'multiple' => false,
                'expanded' => true,
            ])
            ->add('typeDiplomes', EntityType::class, [
                'class' => TypeDiplome::class,
                'choice_label' => 'libelle',
                'label' => 'Type(s) de diplôme proposant ce type d\'UE',
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TypeUe::class,
            'translation_domain' => 'form'
        ]);
    }
}
