<?php

namespace App\Form;

use App\Entity\Domaine;
use App\Entity\Formation;
use App\Entity\Mention;
use App\Entity\User;
use App\Form\Type\YesNoType;
use App\TypeDiplome\TypeDiplomeRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationSesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeDiplome', ChoiceType::class, [
                'choices' => $options['typesDiplomes'],
                'label' => 'Type de diplôme',
                'attr' => ['data-action' => 'change->formation#changeTypeDiplome']
            ])
            ->add('domaine', EntityType::class, [
                'class' => Domaine::class,
                'choice_label' => 'libelle',
                'label' => 'Domaine',
                'attr' => ['data-action' => 'change->formation#changeDomaine']
            ])
            ->add('mention', EntityType::class, [
                'attr' => ['placeholder' => 'Choisir un type de diplôme et un domaine'],
                'class' => Mention::class,
                'choice_label' => 'libelle',
                'label' => 'Mention',
                'required' => false,
                'help' => 'Si la mention n\'existe pas, veuillez la créer dans la section "Autre mention"'
            ])
            ->add('mentionTexte', TextType::class, [
                'label' => 'Autre mention',
                'required' => false,
                'help' => 'Si la mention existe, veuillez la sélectionner dans la liste déroulante'
            ])
            ->add('niveauEntree', ChoiceType::class, [
                'choices' => [
                    'Bac' => 'bac',
                    'Bac + 1' => 'bac+1',
                    'Bac + 2' => 'bac+2',
                    'Bac + 3' => 'bac+3',
                    'Bac + 4' => 'bac+4',
                    'Bac + 5' => 'bac+5',
                    ]])
            ->add('niveauSortie',ChoiceType::class, [
                'choices' => [
                    'Bac' => 'bac',
                    'Bac + 1' => 'bac+1',
                    'Bac + 2' => 'bac+2',
                    'Bac + 3' => 'bac+3',
                    'Bac + 4' => 'bac+4',
                    'Bac + 5' => 'bac+5',
                ]])
            ->add('inscriptionRNCP', YesNoType::class, [
                'label' => 'Inscription au RNCP ?',
                'attr' => ['data-action' => 'change->formation#changeInscriptionRNCP']
            ])
            ->add('codeRNCP', TextType::class, [
                'required' => false,
                'attr' => ['maxlength' => 10],
                'label' => 'Code RNCP'
            ])


            ->add('responsableMention', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'display',
                'label' => 'Responsable de la mention',
                'attr' => ['data-action' => 'change->formation#changeResponsableMention']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
            'typesDiplomes' => []
        ]);
    }
}
