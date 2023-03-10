<?php

namespace App\Form;

use App\Entity\Composante;
use App\Entity\Domaine;
use App\Entity\Formation;
use App\Entity\User;
use App\Enums\NiveauFormationEnum;
use App\Form\Type\YesNoType;
use App\Repository\MentionRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UnitEnum;

class FormationSesType extends AbstractType
{
    public function __construct(
        private readonly TypeDiplomeRegistry $typeDiplomeRegistry,
        private readonly MentionRepository $mentionRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $typeDiplomeRegistry = $this->typeDiplomeRegistry;
        $mentionRepository = $this->mentionRepository;


        $builder
            ->add('typeDiplome', ChoiceType::class, [
                'choices' => $options['typesDiplomes'],
                'attr' => ['data-action' => 'change->formation#changeTypeDiplome']
            ])
            ->add('domaine', EntityType::class, [
                'class' => Domaine::class,
                'choice_label' => 'libelle',
                'attr' => ['data-action' => 'change->formation#changeDomaine']
            ])
            ->add('composantePorteuse', EntityType::class, [
                'attr' => ['placeholder' => 'Choisir la composante porteuse du projet', 'data-action' => 'change->formation#changeComposante'],
                'class' => Composante::class,
                'choice_label' => 'libelle',
//                'label' => '',
                'required' => true,
                'help' => 'Indiquer la composante porteuse du projet, qui aura en charge le d??p??t de la demande de cr??ation de la formation'
            ])
            ->add('mention', ChoiceType::class, [
                'attr' => ['data-action' => 'change->formation#changeMention'],
                'choices' => [
                    'Choisir une mention' => null,
                    'Autre mention' => 'autre'
                ],
                'required' => false,
                'validation_groups' => false,
                'mapped' => false,
                'help' => 'Si la mention n\'existe pas, veuillez la cr??er dans la section "Autre mention"'
            ])
            ->add('mentionTexte', TextType::class, [
                'attr' => ['data-action' => 'change->formation#changeMentionTexte'],
                'required' => false,
                'help' => 'Si la mention existe, veuillez la s??lectionner dans la liste d??roulante'
            ])
            ->add('niveauEntree', EnumType::class, [
                'class' => NiveauFormationEnum::class,
                'choice_label' => static function(UnitEnum $choice): string {
                    return $choice->libelle();
                },
            ])
            ->add('niveauSortie', EnumType::class, [
                'class' => NiveauFormationEnum::class,
                'choice_label' => static function(UnitEnum $choice): string {
                    return $choice->libelle();
                },
            ])
            ->add('inRncp', YesNoType::class, [
                'attr' => ['data-action' => 'change->formation#changeInscriptionRNCP']
            ])
            ->add('codeRNCP', TextType::class, [
                'required' => false,
                'attr' => ['maxlength' => 10],
            ])
            ->add('responsableMention', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'display',
                'attr' => ['data-action' => 'change->formation#changeResponsableMention']
            ])
            ->addEventListener(FormEvents::POST_SUBMIT,
                static function(FormEvent $event) use ($mentionRepository) {
                    $formation = $event->getData();
                    $form = $event->getForm();
                    $mention = $form->get('mention')->getData();
                    if ($mention !== '' && $mention !== null) {
                        $objMention = $mentionRepository->find($mention);
                        $formation->setMention($objMention);
                    } else {
                        $formation->setMention(null);
                    }
                })
            ->addEventListener(FormEvents::PRE_SET_DATA,
                static function(FormEvent $event) use ($typeDiplomeRegistry, $mentionRepository) {
                    $formation = $event->getData();
                    if ($formation->getDomaine() !== null && $formation->getTypeDiplome() !== null) {
                        $form = $event->getForm();
                        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome());
                        $tabMentions = $mentionRepository->findByDomaineAndTypeDiplome($formation->getDomaine(),
                            $typeDiplome);
                        $tabMentions['Autre'] = 'autre';
                        $tabMentions[''] = null;

                        $form->add('mention', ChoiceType::class, [
                            'attr' => ['data-action' => 'change->formation#changeMention'],
                            'choices' => $tabMentions,
                            'label' => 'Mention',
                            'required' => false,
                            'mapped' => false,
                            'help' => 'Si la mention n\'existe pas, veuillez la cr??er dans la section "Autre mention"'
                        ]);
                    }
                });


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
            'typesDiplomes' => [],
            'translation_domain' => 'form'
        ]);
    }
}
