<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file //wsl.localhost/Ubuntu/home/louca/oreof-stack/oreofv2/src/Twig/HelpExtension.php
 * @author louca
 * @project oreofv2
 * @lastUpdate 29/04/2026 15:20
 */


namespace App\Twig;

use App\Entity\Help;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class HelpExtension extends AbstractExtension
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function getFunctions(): array
    {
        return [new TwigFunction('get_page_help', [$this, 'getPageHelp'])];
    }

    public function getPageHelp(string $routeSlug = null): ?Help
    {
        if (!$routeSlug) return null;
        return $this->em->getRepository(Help::class)->findOneBy(['routeSlug' => $routeSlug, 'isActive' => true]);
    }
}