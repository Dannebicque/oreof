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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class HelpType extends AbstractType
{
    public function __construct(private RouterInterface $router)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupération dynamique de toutes les pages (routes)
        $routes = $this->router->getRouteCollection()->all();
        $routeChoices = [];
        foreach ($routes as $name => $route) {
            // On exclut les routes techniques (commençant par _ ou api)
            if (!str_starts_with($name, '_') && !str_starts_with($name, 'api_')) {
                $routeChoices[$name] = $name;
            }
        }

        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'aide'
            ])
            ->add('routeSlug', ChoiceType::class, [
                'choices' => $routeChoices,
                'label' => 'Page cible (Route)',
                'attr' => ['class' => 'select2'] // Compatible avec tes select existants
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu explicatif',
                'attr' => ['rows' => 12]
            ])
            ->add('videoUrl', TextType::class, [
                'label' => 'Lien de la vidéo (YouTube / Vimeo)',
                'required' => false,
                'help' => 'Copiez simplement l\'URL de la vidéo.'
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activer cette aide',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Help::class]);
    }
}
