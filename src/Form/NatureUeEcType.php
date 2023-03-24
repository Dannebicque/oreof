<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/NatureUeEcType.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 02/02/2023 08:52
 */

namespace App\Form;

use App\Entity\NatureUeEc;
use App\Form\Type\YesNoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NatureUeEcType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class)
            ->add('choix', YesNoType::class)
            ->add('libre', YesNoType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NatureUeEc::class,
            'translation_domain' => 'form'
        ]);
    }
}
