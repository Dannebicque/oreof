<?php

namespace App\Form;

use App\Entity\Parcours;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParcoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, [
                'label' => 'Libellé',
                'required' => true,
            ])
            ->add('respParcours', EntityType::class, [
                'label' => 'Responsable du parcours',
                'required' => false,
                'class' => User::class,
                'choice_label' => 'display', //todo: filtrer sur centre de le formation ? ou ajouter un user
            ])
            ->add('sigle', TextType::class, [
                'label' => 'Sigle',
                'help' => 'Optionnel, sigle/code ou appelation courte du parcours',
                'required' => false,
                'attr' => [
                    'maxlength' => '15',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Parcours::class,
        ]);
    }
}
