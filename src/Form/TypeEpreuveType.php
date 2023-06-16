<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/TypeEpreuveType.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 20/02/2023 14:36
 */

namespace App\Form;

use App\Entity\TypeDiplome;
use App\Entity\TypeEpreuve;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeEpreuveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, [
                'label' => 'Libellé',
            ])
            ->add('sigle', TextType::class, [
                'label' => 'Sigle',
            ])
            ->add('typeDiplomes', EntityType::class, [
                'class' =>TypeDiplome::class,
                'choice_label' => 'libelle',
                'label' => 'Type(s) de diplôme proposant ce type d\'épreuve',
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TypeEpreuve::class,
            'typesDiplomes' => [],
            'translation_domain' => 'form'
        ]);
    }
}
