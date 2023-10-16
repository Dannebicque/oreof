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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserHorsUrcaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => true,
                'label' => 'Nom',
                'attr' => ['maxlength' => 255]
            ])
            ->add('prenom', TextType::class, [
                'required' => true,
                'label' => 'Prenom',
                'attr' => ['maxlength' => 255]
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Adresse email',
                'help' => 'Adresse email valide',
                'attr' => ['maxlength' => 255]
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
