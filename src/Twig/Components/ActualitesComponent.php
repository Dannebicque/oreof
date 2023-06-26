<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Twig/Components/AlerteComponent.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 11/03/2023 23:10
 */

namespace App\Twig\Components;

use App\Repository\ActualiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('actualites')]
final class ActualitesComponent extends AbstractController
{
    public function __construct(private ActualiteRepository $actualiteRepository)
    {
    }

    public function getActualites(): array
    {
        return $this->actualiteRepository->findBy(['affiche' => true], ['datePublication' => 'DESC']);
    }
}
