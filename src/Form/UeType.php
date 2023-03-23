<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/UeType.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 21:36
 */

namespace App\Form;

use App\Entity\NatureUeEc;
use App\Entity\TypeUe;
use App\Entity\Ue;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = $options['choices'];


        $builder
            ->add('typeUe', EntityType::class, [
                'class' => TypeUe::class,
                'choice_label' => 'libelle', //todo: filtrer sur diplôme
                'required' => false,
                'mapped' => false,
            ])
            ->add('typeUeTexte', TextType::class, [
                'attr' => [
                    'maxlength' => 100,
                ],
                'required' => false,
                'mapped' => false,
            ])
            ->add('natureUeEc', EntityType::class, [
                'class' => NatureUeEc::class,
                'choice_label' => 'libelle',
                'required' => false,
            ])
            ->add('natureUeEcTexte', TextType::class, [
                'attr' => [
                    'maxlength' => 100,
                ],
                'required' => false,
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ue::class,
            'translation_domain' => 'form',
            'choices' => [],
        ]);
    }
}
