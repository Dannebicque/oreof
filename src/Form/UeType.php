<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/UeType.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 21:36
 */

namespace App\Form;

use App\Entity\TypeEnseignement;
use App\Entity\TypeUe;
use App\Entity\Ue;
use App\Repository\TypeUeRepository;
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
            ->add('ordre', TextType::class, [
            ])
            ->add('typeUe', ChoiceType::class, [
                'choices' => $choices,
                'required' => false,
                'mapped' =>  false,
            ])
            ->add('typeUeTexte', TextType::class, [
                'attr' => [
                    'maxlength' => 100,
                ],
                'required' => false,
                'mapped' => false,
            ])
            ->add('ueObligatoire', EntityType::class, [
                'class' => TypeEnseignement::class,
                'choice_label' => 'libelle',
                'required' => false,
            ])
            ->add('ueObligatoireTexte', TextType::class, [
                'attr' => [
                    'maxlength' => 100,
                ],
                'required' => false,
                'mapped' => false,
            ])
        ;
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
