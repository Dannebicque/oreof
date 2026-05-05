<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file //wsl.localhost/Ubuntu/home/louca/oreof-stack/oreofv2/src/Form/HelpType.php
 * @author louca
 * @project oreofv2
 * @lastUpdate 29/04/2026 15:14
 */


namespace App\Form;

use App\Entity\Help;
use App\Entity\Profil;
use App\Repository\ProfilRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class HelpType extends AbstractType
{
    public function __construct(private RouterInterface $router)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $routes = $this->router->getRouteCollection()->all();
        $routeChoices = [];
        foreach ($routes as $name => $route) {
            if (!str_starts_with($name, '_') && !str_starts_with($name, 'api_')) {
                $routeChoices[$name] = $name;
            }
        }

        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'aide',
                'attr' => ['class' => 'form-control mb-3']
            ])
            ->add('routeSlug', ChoiceType::class, [
                'choices' => $routeChoices,
                'label' => 'Page cible (Route)',
                'attr' => ['class' => 'form-select select2 mb-3']
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu explicatif',
                'attr' => ['class' => 'form-control', 'rows' => 12]
            ])
            ->add('profilsAutorises', EntityType::class, [
                'class' => Profil::class,
                'query_builder' => fn (ProfilRepository $profilRepository) => $profilRepository->createQueryBuilder('p')->orderBy('p.libelle', 'ASC'),
                'choice_label' => fn (Profil $profil) => sprintf('%s (%s)', $profil->getLibelle(), $profil->getCentre()?->getLibelle() ?? 'centre non précisé'),
                'label' => 'Profils autorisés à voir cette aide',
                'help' => 'Laisser vide pour rendre cette aide visible à tous les utilisateurs.',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => ' Activer cette aide',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Help::class]);
    }
}