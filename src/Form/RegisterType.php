<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/RegisterType.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Form;

use App\Entity\User;
use App\Enums\CentreGestionEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => true,
            ])
            ->add('prenom', TextType::class, [
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'help' => '-'
            ])
            ->add('centreDemande', ChoiceType::class, [
                'choices' => [
                    'Composante' => CentreGestionEnum::CENTRE_GESTION_COMPOSANTE,
                    'Etablissement' => CentreGestionEnum::CENTRE_GESTION_ETABLISSEMENT,
                ],
                'placeholder' => 'Indiquez un centre de gestion',
                'required' => true,
                'mapped' => false,
                'help' => '-',
                'attr' => ['data-action' => 'change->register#changeCentre']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'form',
        ]);
    }
}
