<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file //wsl.localhost/Ubuntu/home/louca/oreof-stack/oreofv2/src/Controller/HelpController.php
 * @author louca
 * @project oreofv2
 * @lastUpdate 04/05/2026 16:27
 */

namespace App\Controller;

use App\Entity\Help;
use App\Entity\User;
use App\Repository\HelpRepository;
use App\Service\HelpGrantService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;

class HelpController extends AbstractController
{
    #[Route('/aide', name: 'app_help')]
    public function index(HelpRepository $helpRepository, HelpGrantService $helpGrantService, RouterInterface $router): Response
    {
        $helps = $helpRepository->findBy(['isActive' => true], ['title' => 'ASC']);
        $user = $this->getUser();
        $helps = array_values(array_filter($helps, fn (Help $help) => $helpGrantService->isAllowed($help, $user instanceof User ? $user : null)));

        $previewUrls = [];
        foreach ($helps as $help) {
            $routeName = $help->getRouteSlug();
            $previewUrls[$help->getId()] = null;

            if (!$routeName) {
                continue;
            }

            $route = $router->getRouteCollection()->get($routeName);
            if (!$route) {
                continue;
            }

            if (count($route->compile()->getPathVariables()) > 0) {
                continue;
            }

            try {
                $previewUrls[$help->getId()] = $router->generate($routeName);
            } catch (RouteNotFoundException) {
                $previewUrls[$help->getId()] = null;
            }
        }

        return $this->render('help/index.html.twig', [
            'helps' => $helps,
            'previewUrls' => $previewUrls,
        ]);
    }
}

