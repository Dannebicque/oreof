<?php

namespace App\Form;

use App\Entity\Langue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LangueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, [
                'label' => 'Libellé',
            ])
            ->add('codeIso', TextType::class, [
                'label' => 'Code ISO',
                'help_html' => true,
                'help' => '2 caractères, selon la norme ISO 693-1. <a href="https://fr.wikipedia.org/wiki/Liste_des_codes_ISO_639-1" target="_blank">Liste des codes ISO 639-1</a>',
                'attr' => [
                    'maxlength' => 2,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Langue::class,
        ]);
    }
}
