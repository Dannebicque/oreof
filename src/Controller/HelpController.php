<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file //wsl.localhost/Ubuntu/home/louca/oreof-stack/oreofv2/src/Controller/HelpController.php
 * @author louca
 * @project oreofv2
 * @lastUpdate 04/05/2026 16:27
 */

namespace App\Controller;

use App\Repository\HelpRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HelpController extends AbstractController
{
    #[Route('/aide', name: 'app_help')]
    public function index(HelpRepository $helpRepository): Response
    {
        $helps = $helpRepository->findBy(['isActive' => true], ['title' => 'ASC']);

        return $this->render('help/index.html.twig', [
            'helps' => $helps,
        ]);
    }
}

