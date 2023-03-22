<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/FicheMatiereType.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 21:42
 */

namespace App\Form;

use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\NatureUeEc;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FicheMatiereType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, [
                'required' => true,
                'attr' => ['maxlength' => 250],
            ])
            ->add('responsableFicheMatiere', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'display',
                'required' => true,
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
