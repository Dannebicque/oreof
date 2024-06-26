<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/MentionType.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/02/2023 22:12
 */

namespace App\Form;

use App\Entity\Domaine;
use App\Entity\Mention;
use App\Entity\TypeDiplome;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MentionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeDiplome', EntityType::class, [
                'class' => TypeDiplome::class,
                'query_builder' => static function ($er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.libelle', 'ASC');
                },
                'autocomplete' => true,
                'choice_label' => 'libelle',
                'label' => 'Type de diplôme',
                'attr' => ['data-action' => 'change->formation#changeTypeDiplome']
            ])
            ->add('libelle', TextType::class, [
                'label' => 'Libellé',
                'required' => true,
            ])
            ->add('sigle', TextType::class, [
                'label' => 'Sigle',
                'help' => 'Le sigle est la dénomination courte de la mention, s\'il existe.',
                'required' => false,
            ])
            ->add('domaine', EntityType::class, [
                'query_builder' => static function ($er) {
                    return $er->createQueryBuilder('d')
                        ->orderBy('d.libelle', 'ASC');
                },
                'autocomplete' => true,
                'class' => Domaine::class,
                'choice_label' => 'libelle',
            ])
            ->add('codeApogee', TextType::class, [
                'label' => 'Code Apogée',
                'attr' => ['maxlength' => 1],
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mention::class,
            'translation_domain' => 'form'
        ]);
    }
}
